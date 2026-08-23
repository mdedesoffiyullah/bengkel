<?php

namespace App\Http\Controllers;

use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\StockAllocation;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StockAllocationController extends Controller
{
    /**
     * Menampilkan daftar allocation.
     */
    public function index(Request $request)
    {
        $query = StockAllocation::with([
            'workOrder',
            'product',
        ]);

        if ($request->filled('work_order_id')) {
            $query->where(
                'work_order_id',
                $request->work_order_id
            );
        }

        if ($request->filled('product_id')) {
            $query->where(
                'product_id',
                $request->product_id
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }

        $allocations = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view(
            'stock_allocations.index',
            compact('allocations')
        );
    }

    /**
     * Form membuat allocation.
     */
    public function create(Request $request)
    {
        $workOrder = null;

        if ($request->filled('work_order_id')) {
            $workOrder = WorkOrder::findOrFail(
                $request->work_order_id
            );
        }

        $workOrders = WorkOrder::whereNotIn('status', [
            'COMPLETED',
            'CANCELLED',
        ])
            ->latest()
            ->get();

        $products = Product::where('is_active', true)
            ->where('stock_type', 'STOCK')
            ->orderBy('name')
            ->get();

        return view(
            'stock_allocations.create',
            compact(
                'workOrder',
                'workOrders',
                'products'
            )
        );
    }

    /**
     * Membuat allocation.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'work_order_id' => [
                'required',
                'integer',
                Rule::exists('work_orders', 'id')
                    ->whereNotIn('status', [
                        'COMPLETED',
                        'CANCELLED',
                    ]),
            ],

            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')
                    ->where('is_active', true)
                    ->where('stock_type', 'STOCK'),
            ],

            'quantity' =>
                'required|numeric|gt:0',

            'notes' =>
                'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated) {

                $balance = InventoryBalance::where(
                    'product_id',
                    $validated['product_id']
                )
                    ->lockForUpdate()
                    ->first();

                if (!$balance) {
                    throw new \RuntimeException(
                        'Saldo inventory untuk produk tersebut belum tersedia.'
                    );
                }

                $available =
                    (float) $balance->quantity
                    - (float) $balance->reserved_quantity;

                $requested =
                    (float) $validated['quantity'];

                if ($requested > $available) {
                    throw new \RuntimeException(
                        'Stok tersedia tidak mencukupi untuk allocation.'
                    );
                }

                StockAllocation::create([
                    'work_order_id' =>
                        $validated['work_order_id'],

                    'product_id' =>
                        $validated['product_id'],

                    'quantity' =>
                        $requested,

                    'status' =>
                        'RESERVED',

                    'notes' =>
                        $validated['notes'] ?? null,
                ]);

                $balance->increment(
                    'reserved_quantity',
                    $requested
                );

                $balance->update([
                    'available_quantity' =>
                        (float) $balance->quantity
                        - (
                            (float) $balance->reserved_quantity
                            + $requested
                        ),
                ]);
            });

        } catch (\RuntimeException $e) {
            return back()
                ->withErrors([
                    'quantity' => $e->getMessage(),
                ])
                ->withInput();
        }

        return redirect()
            ->route(
                'work-orders.show',
                $validated['work_order_id']
            )
            ->with(
                'success',
                'Stok berhasil dialokasikan ke Work Order.'
            );
    }

    /**
     * Detail allocation.
     */
    public function show(StockAllocation $stockAllocation)
    {
        $stockAllocation->load([
            'workOrder',
            'product',
        ]);

        return view(
            'stock_allocations.show',
            compact('stockAllocation')
        );
    }

    /**
     * Allocation tidak diedit langsung.
     */
    public function edit(
        StockAllocation $stockAllocation
    ) {
        return redirect()
            ->route(
                'stock-allocations.show',
                $stockAllocation
            )
            ->with(
                'error',
                'Allocation tidak diedit langsung. Gunakan proses release/cancel allocation.'
            );
    }

    /**
     * Update allocation tidak diperbolehkan.
     */
    public function update(
        Request $request,
        StockAllocation $stockAllocation
    ) {
        return redirect()
            ->route(
                'stock-allocations.show',
                $stockAllocation
            )
            ->with(
                'error',
                'Allocation tidak dapat diubah langsung.'
            );
    }

    /**
     * Menghapus / release allocation.
     */
    public function destroy(
        StockAllocation $stockAllocation
    ) {
        if ($stockAllocation->status !== 'RESERVED') {
            return redirect()
                ->route(
                    'stock-allocations.show',
                    $stockAllocation
                )
                ->with(
                    'error',
                    'Allocation ini tidak lagi berstatus RESERVED.'
                );
        }

        DB::transaction(function () use ($stockAllocation) {

            $balance = InventoryBalance::where(
                'product_id',
                $stockAllocation->product_id
            )
                ->lockForUpdate()
                ->first();

            if ($balance) {
                $quantity =
                    (float) $stockAllocation->quantity;

                $balance->decrement(
                    'reserved_quantity',
                    $quantity
                );

                $balance->update([
                    'available_quantity' =>
                        (float) $balance->quantity
                        - (
                            (float) $balance->reserved_quantity
                            - $quantity
                        ),
                ]);
            }

            $stockAllocation->update([
                'status' => 'CANCELLED',
            ]);
        });

        return redirect()
            ->route('stock-allocations.index')
            ->with(
                'success',
                'Allocation berhasil dibatalkan dan stok dikembalikan ke available.'
            );
    }
}