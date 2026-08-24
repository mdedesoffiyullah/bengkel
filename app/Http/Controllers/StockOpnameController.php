<?php

namespace App\Http\Controllers;

use App\Models\StockOpname;
use App\Models\InventoryBalance;
use App\Models\InventoryLayer;
use App\Models\Product;
use App\Models\StockOpnameItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StockOpnameController extends Controller
{
    public function index(Request $request)
    {
        $query = StockOpname::withCount('items');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $stockOpnames = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view(
            'stock_opnames.index',
            compact('stockOpnames')
        );
    }

    public function create()
    {
        $products = Product::where('is_active', true)
            ->with(['inventoryBalance', 'inventoryLayers'])
            ->orderBy('name')
            ->get();

        return view(
            'stock_opnames.create',
            compact('products')
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                'unique:stock_opnames,code',
            ],

            'opname_date' => 'required|date',

            'notes' => 'nullable|string',

            'status' => [
                'nullable',
                Rule::in([
                    'DRAFT',
                    'IN_PROGRESS',
                ]),
            ],
        ]);

        $opname = StockOpname::create([
            'code' => $validated['code'],
            'opname_date' => $validated['opname_date'],
            'notes' => $validated['notes'] ?? null,
            'status' => $validated['status'] ?? 'DRAFT',
        ]);

        return redirect()
            ->route('stock-opnames.show', $opname)
            ->with(
                'success',
                'Stock opname berhasil dibuat.'
            );
    }

    public function show(StockOpname $stockOpname)
    {
        $stockOpname->load([
            'items.product',
        ]);

        $products = Product::where('is_active', true)
            ->with(['inventoryBalance', 'inventoryLayers'])
            ->orderBy('name')
            ->get();

        return view(
            'stock_opnames.show',
            compact(
                'stockOpname',
                'products'
            )
        );
    }

    public function addItem(
        Request $request,
        StockOpname $stockOpname
    ) {
        if (
            in_array(
                $stockOpname->status,
                [
                    'POSTED',
                    'CANCELLED',
                ]
            )
        ) {
            return back()
                ->with(
                    'error',
                    'Stock opname yang sudah final tidak dapat diubah.'
                );
        }

        $validated = $request->validate([
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')
                    ->where('is_active', true),
            ],

            'physical_quantity' =>
                'required|numeric|min:0',

            'notes' =>
                'nullable|string',
        ]);

        $productId = (int) $validated['product_id'];

        /*
         * Sumber stok utama:
         * InventoryBalance.
         *
         * Jika balance belum tersedia / belum sinkron,
         * fallback ke jumlah layer yang masih tersisa.
         */
        $balance = InventoryBalance::where(
            'product_id',
            $productId
        )->first();

        $layerQuery = InventoryLayer::where(
            'product_id',
            $productId
        )
            ->where('remaining_quantity', '>', 0);

        $layerQuantity = (float) $layerQuery->sum(
            'remaining_quantity'
        );

        /*
         * Jika balance ada, gunakan balance.
         * Jika quantity balance 0 tetapi layer masih punya
         * stok, gunakan layer sebagai fallback.
         */
        if (
            $balance &&
            (int) $balance->quantity > 0
        ) {
            $systemQuantity =
                (int) $balance->quantity;

            $unitCost =
                (float) $balance->average_cost;
        } else {
            $systemQuantity =
                $layerQuantity;

            /*
             * Weighted average cost dari layer yang
             * masih memiliki stok.
             */
            $layerCostTotal = (float) (
                (clone $layerQuery)
                    ->selectRaw(
                        'COALESCE(SUM(remaining_quantity * unit_cost), 0) as total_cost'
                    )
                    ->value('total_cost')
            );

            $unitCost = $systemQuantity > 0
                ? $layerCostTotal / $systemQuantity
                : 0;
        }

        $physicalQuantity =
            (int) $validated['physical_quantity'];

        $differenceQuantity =
            $physicalQuantity - $systemQuantity;

        $differenceValue =
            $differenceQuantity * $unitCost;

        StockOpnameItem::updateOrCreate(
            [
                'stock_opname_id' =>
                    $stockOpname->id,

                'product_id' =>
                    $productId,
            ],
            [
                'system_quantity' =>
                    $systemQuantity,

                'physical_quantity' =>
                    $physicalQuantity,

                'difference_quantity' =>
                    $differenceQuantity,

                'unit_cost' =>
                    $unitCost,

                'difference_value' =>
                    $differenceValue,

                'notes' =>
                    $validated['notes'] ?? null,
            ]
        );

        return redirect()
            ->route(
                'stock-opnames.show',
                $stockOpname
            )
            ->with(
                'success',
                'Item stock opname berhasil ditambahkan.'
            );
    }

    public function edit(
        StockOpname $stockOpname
    ) {
        if (
            in_array(
                $stockOpname->status,
                [
                    'POSTED',
                    'CANCELLED',
                ]
            )
        ) {
            return redirect()
                ->route(
                    'stock-opnames.show',
                    $stockOpname
                )
                ->with(
                    'error',
                    'Stock opname yang sudah final tidak dapat diedit.'
                );
        }

        return view(
            'stock_opnames.edit',
            compact('stockOpname')
        );
    }

    public function update(
        Request $request,
        StockOpname $stockOpname
    ) {
        if (
            in_array(
                $stockOpname->status,
                [
                    'POSTED',
                    'CANCELLED',
                ]
            )
        ) {
            return back()
                ->with(
                    'error',
                    'Stock opname yang sudah final tidak dapat diubah.'
                );
        }

        $validated = $request->validate([
            'opname_date' => 'required|date',

            'notes' => 'nullable|string',

            'status' => [
                'required',
                Rule::in([
                    'DRAFT',
                    'IN_PROGRESS',
                ]),
            ],
        ]);

        $stockOpname->update($validated);

        return redirect()
            ->route(
                'stock-opnames.show',
                $stockOpname
            )
            ->with(
                'success',
                'Stock opname berhasil diperbarui.'
            );
    }

    public function destroy(
        StockOpname $stockOpname
    ) {
        if (
            $stockOpname->status === 'POSTED'
        ) {
            return back()
                ->with(
                    'error',
                    'Stock opname yang sudah diposting tidak dapat dibatalkan.'
                );
        }

        $stockOpname->update([
            'status' => 'CANCELLED',
        ]);

        return redirect()
            ->route('stock-opnames.index')
            ->with(
                'success',
                'Stock opname berhasil dibatalkan.'
            );
    }
}

