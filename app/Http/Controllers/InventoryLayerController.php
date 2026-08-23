<?php

namespace App\Http\Controllers;

use App\Models\InventoryLayer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InventoryLayerController extends Controller
{
    /**
     * Menampilkan inventory layers.
     */
    public function index(Request $request)
    {
        $query = InventoryLayer::with([
            'product',
            'purchaseItem',
            'stockMovement',
        ]);

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

        $layers = $query
            ->orderBy('received_at')
            ->paginate(20)
            ->withQueryString();

        return view(
            'inventory_layers.index',
            compact('layers')
        );
    }

    /**
     * Form membuat inventory layer.
     *
     * Normalnya layer dibuat otomatis oleh proses
     * penerimaan barang / stock movement.
     */
    public function create()
    {
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'inventory_layers.create',
            compact('products')
        );
    }

    /**
     * Membuat layer secara manual.
     *
     * Digunakan untuk saldo awal / adjustment tertentu.
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

            'quantity' => 'required|numeric|gt:0',

            'remaining_quantity' =>
                'required|numeric|gt:0',

            'unit_cost' =>
                'required|numeric|min:0',

            'received_at' =>
                'required|date',

            'status' => [
                'nullable',
                Rule::in([
                    'OPEN',
                    'DEPLETED',
                    'CANCELLED',
                ]),
            ],
        ]);

        $quantity =
            (float) $validated['quantity'];

        $remainingQuantity =
            (float) $validated['remaining_quantity'];

        $unitCost =
            (float) $validated['unit_cost'];

        /*
         * Remaining quantity tidak boleh
         * melebihi quantity awal.
         */
        if ($remainingQuantity > $quantity) {
            return back()
                ->withErrors([
                    'remaining_quantity' =>
                        'Remaining quantity tidak boleh lebih besar dari quantity layer.',
                ])
                ->withInput();
        }

        /*
         * Status otomatis berdasarkan remaining quantity.
         */
        if ($remainingQuantity <= 0) {
            $status = 'DEPLETED';
        } else {
            $status = 'OPEN';
        }

        $layer = InventoryLayer::create([
            'product_id' =>
                $validated['product_id'],

            'purchase_item_id' =>
                $validated['purchase_item_id'] ?? null,

            'stock_movement_id' =>
                $validated['stock_movement_id'] ?? null,

            'quantity' =>
                $quantity,

            'remaining_quantity' =>
                $remainingQuantity,

            'unit_cost' =>
                $unitCost,

            'total_cost' =>
                $quantity * $unitCost,

            'received_at' =>
                $validated['received_at'],

            'status' =>
                $status,
        ]);

        return redirect()
            ->route('inventory-layers.show', $layer)
            ->with(
                'success',
                'Inventory layer berhasil dibuat.'
            );
    }

    /**
     * Menampilkan detail layer.
     */
    public function show(InventoryLayer $inventoryLayer)
    {
        $inventoryLayer->load([
            'product',
            'purchaseItem',
            'stockMovement',
        ]);

        return view(
            'inventory_layers.show',
            compact('inventoryLayer')
        );
    }

    /**
     * Layer tidak boleh diedit secara manual.
     */
    public function edit(
        InventoryLayer $inventoryLayer
    ) {
        return redirect()
            ->route(
                'inventory-layers.show',
                $inventoryLayer
            )
            ->with(
                'error',
                'Inventory layer tidak diedit langsung. Perubahan dilakukan melalui transaksi inventory.'
            );
    }

    /**
     * Update layer tidak diperbolehkan.
     *
     * Remaining quantity harus berubah melalui
     * proses FIFO allocation.
     */
    public function update(
        Request $request,
        InventoryLayer $inventoryLayer
    ) {
        return redirect()
            ->route(
                'inventory-layers.show',
                $inventoryLayer
            )
            ->with(
                'error',
                'Inventory layer tidak dapat diubah langsung.'
            );
    }

    /**
     * Layer tidak boleh dihapus sembarangan.
     */
    public function destroy(
        InventoryLayer $inventoryLayer
    ) {
        if (
            $inventoryLayer->status === 'OPEN'
            && $inventoryLayer->remaining_quantity > 0
        ) {
            return redirect()
                ->route(
                    'inventory-layers.show',
                    $inventoryLayer
                )
                ->with(
                    'error',
                    'Layer yang masih memiliki stok tidak dapat dihapus.'
                );
        }

        $inventoryLayer->delete();

        return redirect()
            ->route('inventory-layers.index')
            ->with(
                'success',
                'Inventory layer berhasil dihapus.'
            );
    }
}