<?php

namespace App\Http\Controllers;

use App\Models\InventoryBalance;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InventoryBalanceController extends Controller
{
    /**
     * Menampilkan saldo inventory.
     */
    public function index(Request $request)
    {
        $query = InventoryBalance::with('product');

        if ($request->filled('product_id')) {
            $query->where(
                'product_id',
                $request->product_id
            );
        }

        $balances = $query
            ->orderBy('product_id')
            ->paginate(20)
            ->withQueryString();

        return view(
            'inventory.index',
            compact('balances')
        );
    }

    /**
     * Form membuat saldo inventory.
     *
     * Biasanya tidak digunakan untuk transaksi normal.
     * Saldo akan dibuat/diperbarui melalui Stock Movement.
     */
    public function create()
    {
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'inventory_balances.create',
            compact('products')
        );
    }

    /**
     * Membuat saldo awal inventory.
     *
     * Digunakan untuk initial stock/setup,
     * bukan transaksi stok harian.
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

            'quantity' => 'required|integer|min:0',

            'average_cost' => 'required|numeric|min:0',

            'notes' => 'nullable|string',
        ]);

        $balance = InventoryBalance::firstOrCreate(
            [
                'product_id' =>
                    $validated['product_id'],
            ],
            [
                'quantity' => 0,
                'average_cost' => 0,
                'total_cost' => 0,
            ]
        );

        /*
         * Saldo awal hanya boleh dibuat jika
         * balance masih kosong.
         */
        if ((int) $balance->quantity > 0) {
            return back()
                ->withErrors([
                    'product_id' =>
                        'Saldo inventory untuk product ini sudah tersedia.',
                ])
                ->withInput();
        }

        $quantity =
            (int) $validated['quantity'];

        $averageCost =
            (float) $validated['average_cost'];

        $balance->update([
            'quantity' => $quantity,
            'average_cost' => $averageCost,
            'total_cost' =>
                $quantity * $averageCost,
        ]);

        return redirect()
            ->route('inventory-balances.index')
            ->with(
                'success',
                'Saldo awal inventory berhasil dibuat.'
            );
    }

    /**
     * Menampilkan detail saldo inventory.
     */
    public function show(
        InventoryBalance $inventoryBalance
    ) {
        $inventoryBalance->load([
            'product',
        ]);

        return view(
            'inventory_balances.show',
            compact('inventoryBalance')
        );
    }

    /**
     * Edit saldo inventory.
     *
     * Sengaja dibatasi karena perubahan saldo
     * harus melalui Stock Movement.
     */
    public function edit(
        InventoryBalance $inventoryBalance
    ) {
        return redirect()
            ->route(
                'inventory-balances.show',
                $inventoryBalance
            )
            ->with(
                'error',
                'Saldo inventory tidak diedit langsung. Gunakan Stock Movement.'
            );
    }

    /**
     * Update saldo inventory.
     *
     * Tidak digunakan untuk transaksi normal.
     */
    public function update(
        Request $request,
        InventoryBalance $inventoryBalance
    ) {
        return redirect()
            ->route(
                'inventory-balances.show',
                $inventoryBalance
            )
            ->with(
                'error',
                'Saldo inventory tidak dapat diubah langsung. Gunakan Stock Movement.'
            );
    }

    /**
     * Hapus saldo inventory.
     *
     * Tidak diperbolehkan jika saldo sudah digunakan.
     */
    public function destroy(
        InventoryBalance $inventoryBalance
    ) {
        if ((int) $inventoryBalance->quantity > 0) {
            return redirect()
                ->route(
                    'inventory-balances.show',
                    $inventoryBalance
                )
                ->with(
                    'error',
                    'Saldo inventory dengan quantity masih tersedia tidak dapat dihapus.'
                );
        }

        $inventoryBalance->delete();

        return redirect()
            ->route('inventory-balances.index')
            ->with(
                'success',
                'Saldo inventory berhasil dihapus.'
            );
    }
}



