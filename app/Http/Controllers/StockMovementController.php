<?php

namespace App\Http\Controllers;

use App\Models\InventoryBalance;
use App\Models\InventoryLayer;
use App\Models\Product;
use App\Models\StockAllocation;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StockMovementController extends Controller
{
    /**
     * Menampilkan daftar stock movement.
     */
    public function index(Request $request)
    {
        $query = StockMovement::with('product');

        if ($request->filled('product_id')) {
            $query->where(
                'product_id',
                $request->product_id
            );
        }

        if ($request->filled('type')) {
            $query->where(
                'type',
                $request->type
            );
        }

        $movements = $query
            ->latest('moved_at')
            ->paginate(20)
            ->withQueryString();

        return view(
            'stock_movements.index',
            compact('movements')
        );
    }

    /**
     * Form stock movement.
     */
    public function create()
    {
        $products = Product::where(
            'is_active',
            true
        )
            ->orderBy('name')
            ->get();

        $allocations = StockAllocation::with([
            'product',
            'workOrder',
        ])
            ->where('status', 'RESERVED')
            ->latest()
            ->get();

        return view(
            'stock_movements.create',
            compact(
                'products',
                'allocations'
            )
        );
    }

    /**
     * Membuat stock movement.
     *
     * INPUT FORM:
     * IN         -> PURCHASE
     * OUT        -> USAGE
     * ADJUSTMENT -> ADJUSTMENT
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')
                    ->where('is_active', true),
            ],

            'type' => [
                'required',
                Rule::in([
                    'IN',
                    'OUT',
                    'ADJUSTMENT',
                ]),
            ],

            'quantity' =>
                'required|numeric|gt:0',

            'unit_cost' =>
                'nullable|numeric|min:0',

            'stock_allocation_id' => [
                'nullable',
                'integer',
                'exists:stock_allocations,id',
            ],

            'reference_type' =>
                'nullable|string|max:50',

            'reference_id' =>
                'nullable|integer',

            'movement_date' =>
                'required|date',

            'notes' =>
                'nullable|string',
        ]);

        try {
            $movement = DB::transaction(
                function () use ($validated) {

                    $quantity =
                        (int) $validated['quantity'];

                    /*
                     * Mapping tipe dari form ke enum database.
                     */
                    $movementType = match (
                        $validated['type']
                    ) {
                        'IN' =>
                            'PURCHASE',

                        'OUT' =>
                            'USAGE',

                        'ADJUSTMENT' =>
                            'ADJUSTMENT',
                    };

                    /*
                     * LOCK inventory balance.
                     */
                    $balance =
                        InventoryBalance::where(
                            'product_id',
                            $validated['product_id']
                        )
                            ->lockForUpdate()
                            ->first();

                    /*
                     * Jika balance belum ada,
                     * buat balance kosong.
                     */
                    if (!$balance) {
                        $balance =
                            InventoryBalance::create([
                                'product_id' =>
                                    $validated['product_id'],

                                'quantity' =>
                                    0,

                                'reserved_quantity' =>
                                    0,

                                'available_quantity' =>
                                    0,

                                'average_cost' =>
                                    0,
                            ]);
                    }

                    $createdLayers = [];
                    $totalCost = 0;

                    /*
                     * =========================================
                     * STOCK IN / PURCHASE
                     * =========================================
                     */
                    if (
                        $validated['type'] === 'IN'
                    ) {
                        $unitCost =
                            (float) (
                                $validated['unit_cost']
                                ?? 0
                            );

                        $layer =
                            InventoryLayer::create([
                                'product_id' =>
                                    $validated['product_id'],

                                'purchase_item_id' =>
                                    null,

                                'stock_movement_id' =>
                                    null,

                                'quantity' =>
                                    $quantity,

                                'remaining_quantity' =>
                                    $quantity,

                                'unit_cost' =>
                                    $unitCost,

                                'total_cost' =>
                                    $quantity * $unitCost,

                                'received_at' =>
                                    $validated['movement_date'],

                                'status' =>
                                    'ACTIVE',
                            ]);

                        $createdLayers[] =
                            $layer;

                        $oldQuantity =
                            (int) $balance->quantity;

                        $oldAverageCost =
                            (float) $balance->average_cost;

                        $oldTotalCost =
                            $oldQuantity
                            * $oldAverageCost;

                        $newQuantity =
                            $oldQuantity
                            + $quantity;

                        $newTotalCost =
                            $oldTotalCost
                            + (
                                $quantity
                                * $unitCost
                            );

                        $averageCost =
                            $newQuantity > 0
                                ? $newTotalCost
                                    / $newQuantity
                                : 0;

                        $balance->update([
                            'quantity' =>
                                $newQuantity,

                            'average_cost' =>
                                $averageCost,

                            'available_quantity' =>
                                $newQuantity
                                - (
                                    (float)
                                    $balance
                                        ->reserved_quantity
                                ),
                        ]);

                        $totalCost =
                            $quantity
                            * $unitCost;
                    }

                    /*
                     * =========================================
                     * STOCK OUT / USAGE
                     * =========================================
                     */
                    if (
                        $validated['type'] === 'OUT'
                    ) {
                        $available =
                            (int) $balance->quantity
                            - (
                                (float)
                                $balance
                                    ->reserved_quantity
                            );

                        $allocation = null;

                        /*
                         * Jika menggunakan allocation,
                         * reserved stock boleh digunakan.
                         */
                        if (
                            !empty(
                                $validated[
                                    'stock_allocation_id'
                                ]
                            )
                        ) {
                            $allocation =
                                StockAllocation::where(
                                    'id',
                                    $validated[
                                        'stock_allocation_id'
                                    ]
                                )
                                    ->lockForUpdate()
                                    ->firstOrFail();

                            if (
                                $allocation->status
                                !== 'RESERVED'
                            ) {
                                throw new \RuntimeException(
                                    'Stock allocation tidak berstatus RESERVED.'
                                );
                            }

                            if (
                                $allocation->product_id
                                !=
                                $validated['product_id']
                            ) {
                                throw new \RuntimeException(
                                    'Product tidak sesuai dengan stock allocation.'
                                );
                            }

                            if (
                                $quantity
                                >
                                (
                                    (float)
                                    $allocation->quantity
                                )
                            ) {
                                throw new \RuntimeException(
                                    'Quantity OUT melebihi quantity allocation.'
                                );
                            }

                            /*
                             * Pastikan stok fisik tetap cukup.
                             */
                            if (
                                $quantity
                                >
                                (
                                    (float)
                                    $balance->quantity
                                )
                            ) {
                                throw new \RuntimeException(
                                    'Stok fisik tidak mencukupi.'
                                );
                            }
                        } elseif (
                            $quantity > $available
                        ) {
                            throw new \RuntimeException(
                                'Stok available tidak mencukupi.'
                            );
                        }

                        /*
                         * FIFO:
                         * layer tertua digunakan terlebih dahulu.
                         */
                        $layers =
                            InventoryLayer::where(
                                'product_id',
                                $validated['product_id']
                            )
                                ->where(
                                    'status',
                                    'ACTIVE'
                                )
                                ->where(
                                    'remaining_quantity',
                                    '>',
                                    0
                                )
                                ->orderBy(
                                    'received_at',
                                    'asc'
                                )
                                ->orderBy(
                                    'id',
                                    'asc'
                                )
                                ->lockForUpdate()
                                ->get();

                        $remainingToConsume =
                            $quantity;

                        foreach ($layers as $layer) {

                            if (
                                $remainingToConsume
                                <= 0
                            ) {
                                break;
                            }

                            $availableLayer =
                                (float)
                                $layer->remaining_quantity;

                            $consume =
                                min(
                                    $remainingToConsume,
                                    $availableLayer
                                );

                            $totalCost +=
                                $consume
                                * (
                                    (float)
                                    $layer->unit_cost
                                );

                            $newRemaining =
                                $availableLayer
                                - $consume;

                            $layer->update([
                                'remaining_quantity' =>
                                    $newRemaining,

                                'status' =>
                                    $newRemaining > 0
                                        ? 'ACTIVE'
                                        : 'DEPLETED',
                            ]);

                            $createdLayers[] =
                                $layer;

                            $remainingToConsume -=
                                $consume;
                        }

                        if (
                            $remainingToConsume > 0
                        ) {
                            throw new \RuntimeException(
                                'Inventory layer tidak memiliki stok yang cukup.'
                            );
                        }

                        /*
                         * Kurangi quantity fisik.
                         */
                        $oldQuantity =
                            (int) $balance->quantity;

                        $newQuantity =
                            $oldQuantity
                            - $quantity;

                        $oldTotalCost =
                            (float)
                            $balance->average_cost
                            * $oldQuantity;

                        $newTotalCost =
                            max(
                                0,
                                $oldTotalCost
                                - $totalCost
                            );

                        $newAverageCost =
                            $newQuantity > 0
                                ? $newTotalCost
                                    / $newQuantity
                                : 0;

                        $newReserved =
                            (float)
                            $balance->reserved_quantity;

                        /*
                         * Kurangi reserved jika
                         * movement berasal dari allocation.
                         */
                        if ($allocation) {
                            $newReserved =
                                max(
                                    0,
                                    $newReserved
                                    - $quantity
                                );

                            $allocationRemaining =
                                max(
                                    0,
                                    (
                                        (float)
                                        $allocation->quantity
                                    )
                                    - $quantity
                                );

                            $allocation->update([
                                'quantity' =>
                                    $allocationRemaining,

                                'status' =>
                                    $allocationRemaining > 0
                                        ? 'RESERVED'
                                        : 'CONSUMED',
                            ]);
                        }

                        $balance->update([
                            'quantity' =>
                                $newQuantity,

                            'average_cost' =>
                                $newAverageCost,

                            'reserved_quantity' =>
                                $newReserved,

                            'available_quantity' =>
                                max(
                                    0,
                                    $newQuantity
                                    - $newReserved
                                ),
                        ]);
                    }

                    /*
                     * =========================================
                     * ADJUSTMENT
                     * =========================================
                     *
                     * Untuk sekarang adjustment dianggap
                     * sebagai penambahan stok.
                     */
                    if (
                        $validated['type']
                        === 'ADJUSTMENT'
                    ) {
                        $unitCost =
                            (float) (
                                $validated['unit_cost']
                                ??
                                $balance->average_cost
                            );

                        $layer =
                            InventoryLayer::create([
                                'product_id' =>
                                    $validated['product_id'],

                                'purchase_item_id' =>
                                    null,

                                'stock_movement_id' =>
                                    null,

                                'quantity' =>
                                    $quantity,

                                'remaining_quantity' =>
                                    $quantity,

                                'unit_cost' =>
                                    $unitCost,

                                'total_cost' =>
                                    $quantity
                                    * $unitCost,

                                'received_at' =>
                                    $validated['movement_date'],

                                'status' =>
                                    'ACTIVE',
                            ]);

                        $createdLayers[] =
                            $layer;

                        $oldQuantity =
                            (int) $balance->quantity;

                        $oldAverageCost =
                            (float) $balance->average_cost;

                        $oldTotalCost =
                            $oldQuantity
                            * $oldAverageCost;

                        $newQuantity =
                            $oldQuantity
                            + $quantity;

                        $newTotalCost =
                            $oldTotalCost
                            + (
                                $quantity
                                * $unitCost
                            );

                        $balance->update([
                            'quantity' =>
                                $newQuantity,

                            'average_cost' =>
                                $newQuantity > 0
                                    ? $newTotalCost
                                        / $newQuantity
                                    : 0,

                            'available_quantity' =>
                                $newQuantity
                                - (
                                    (float)
                                    $balance
                                        ->reserved_quantity
                                ),
                        ]);

                        $totalCost =
                            $quantity
                            * $unitCost;
                    }

                    /*
                     * Buat stock movement setelah
                     * inventory berhasil dihitung.
                     */
                    $movement =
                        StockMovement::create([
                            'product_id' =>
                                $validated['product_id'],

                            'type' =>
                                $movementType,

                            'quantity' =>
                                $quantity,

                            'unit_cost' =>
                                $validated['unit_cost']
                                ?? (
                                    $validated['type'] === 'OUT'
                                        && $quantity > 0
                                        ? $totalCost
                                            / $quantity
                                        : 0
                                ),

                            'reference_type' =>
                                $validated[
                                    'reference_type'
                                ] ?? (
                                    !empty(
                                        $validated[
                                            'stock_allocation_id'
                                        ]
                                    )
                                        ? 'stock_allocation'
                                        : null
                                ),

                            'reference_id' =>
                                $validated[
                                    'reference_id'
                                ] ?? (
                                    $validated[
                                        'stock_allocation_id'
                                    ] ?? null
                                ),

                            'moved_at' =>
                                $validated['movement_date'],

                            'notes' =>
                                $validated['notes']
                                ?? null,
                        ]);

                    /*
                     * Hubungkan inventory layer
                     * dengan stock movement.
                     */
                    foreach (
                        $createdLayers
                        as $layer
                    ) {
                        $layer->update([
                            'stock_movement_id' =>
                                $movement->id,
                        ]);
                    }

                    return $movement;
                }
            );

        } catch (\RuntimeException $e) {

            return back()
                ->withErrors([
                    'quantity' =>
                        $e->getMessage(),
                ])
                ->withInput();
        }

        return redirect()
            ->route(
                'stock-movements.show',
                $movement
            )
            ->with(
                'success',
                'Stock movement berhasil diposting.'
            );
    }

    /**
     * Detail stock movement.
     */
    public function show(
        StockMovement $stockMovement
    ) {
        $stockMovement->load([
            'product',
            'inventoryLayers',
        ]);

        return view(
            'stock_movements.show',
            compact('stockMovement')
        );
    }

    /**
     * Movement yang sudah tercatat tidak boleh diedit.
     */
    public function edit(
        StockMovement $stockMovement
    ) {
        return redirect()
            ->route(
                'stock-movements.show',
                $stockMovement
            )
            ->with(
                'error',
                'Stock movement yang sudah tercatat tidak dapat diedit.'
            );
    }

    /**
     * Movement tidak boleh diubah setelah tercatat.
     */
    public function update(
        Request $request,
        StockMovement $stockMovement
    ) {
        return redirect()
            ->route(
                'stock-movements.show',
                $stockMovement
            )
            ->with(
                'error',
                'Stock movement tidak dapat diubah setelah tercatat.'
            );
    }

    /**
     * Movement tidak boleh dihapus.
     */
    public function destroy(
        StockMovement $stockMovement
    ) {
        return redirect()
            ->route(
                'stock-movements.show',
                $stockMovement
            )
            ->with(
                'error',
                'Stock movement tidak dapat dihapus. Gunakan reversal atau adjustment.'
            );
    }
}

