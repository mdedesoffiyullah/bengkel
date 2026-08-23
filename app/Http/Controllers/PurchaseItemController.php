<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PurchaseItemController extends Controller
{
    /**
     * Menampilkan daftar item purchase.
     */
    public function index(Request $request)
    {
        $query = PurchaseItem::with([
            'purchase',
            'product',
        ]);

        if ($request->filled('purchase_id')) {
            $query->where(
                'purchase_id',
                $request->purchase_id
            );
        }

        $items = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view(
            'purchase_items.index',
            compact('items')
        );
    }

    /**
     * Form tambah item purchase.
     */
    public function create(Request $request)
    {
        $purchase = null;

        if ($request->filled('purchase_id')) {
            $purchase = Purchase::findOrFail(
                $request->purchase_id
            );
        }

        $purchases = Purchase::whereNotIn('status', [
            'RECEIVED',
            'CANCELLED',
        ])
            ->latest()
            ->get();

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'purchase_items.create',
            compact(
                'purchase',
                'purchases',
                'products'
            )
        );
    }

    /**
     * Menyimpan item purchase.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_id' => [
                'required',
                'integer',
                Rule::exists('purchases', 'id')
                    ->whereNotIn('status', [
                        'RECEIVED',
                        'CANCELLED',
                    ]),
            ],

            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')
                    ->where('is_active', true),
            ],

            'quantity' => 'required|numeric|min:0.001',

            'unit_cost' => 'required|numeric|min:0',

            'discount' => 'nullable|numeric|min:0',

            'received_quantity' => 'nullable|numeric|min:0',

            'notes' => 'nullable|string',
        ]);

        $quantity = (float) $validated['quantity'];
        $unitCost = (float) $validated['unit_cost'];
        $discount = (float) ($validated['discount'] ?? 0);

        $subtotal = ($quantity * $unitCost) - $discount;

        if ($subtotal < 0) {
            return back()
                ->withErrors([
                    'discount' =>
                        'Diskon tidak boleh lebih besar dari nilai item.',
                ])
                ->withInput();
        }

        if (
            isset($validated['received_quantity'])
            && $validated['received_quantity'] > $quantity
        ) {
            return back()
                ->withErrors([
                    'received_quantity' =>
                        'Quantity diterima tidak boleh lebih besar dari quantity pembelian.',
                ])
                ->withInput();
        }

        $validated['discount'] = $discount;
        $validated['subtotal'] = $subtotal;
        $validated['received_quantity'] =
            $validated['received_quantity'] ?? 0;

        PurchaseItem::create($validated);

        $this->recalculatePurchaseTotal(
            $validated['purchase_id']
        );

        return redirect()
            ->route(
                'purchases.show',
                $validated['purchase_id']
            )
            ->with(
                'success',
                'Item purchase berhasil ditambahkan.'
            );
    }

    /**
     * Menampilkan detail item purchase.
     */
    public function show(PurchaseItem $purchaseItem)
    {
        $purchaseItem->load([
            'purchase',
            'product',
        ]);

        return view(
            'purchase_items.show',
            compact('purchaseItem')
        );
    }

    /**
     * Form edit item purchase.
     */
    public function edit(PurchaseItem $purchaseItem)
    {
        if (
            in_array(
                $purchaseItem->purchase->status,
                ['RECEIVED', 'CANCELLED']
            )
        ) {
            return redirect()
                ->route(
                    'purchases.show',
                    $purchaseItem->purchase_id
                )
                ->with(
                    'error',
                    'Item pada purchase ini tidak dapat diedit.'
                );
        }

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'purchase_items.edit',
            compact(
                'purchaseItem',
                'products'
            )
        );
    }

    /**
     * Memperbarui item purchase.
     */
    public function update(
        Request $request,
        PurchaseItem $purchaseItem
    ) {
        if (
            in_array(
                $purchaseItem->purchase->status,
                ['RECEIVED', 'CANCELLED']
            )
        ) {
            return redirect()
                ->route(
                    'purchases.show',
                    $purchaseItem->purchase_id
                )
                ->with(
                    'error',
                    'Item pada purchase ini tidak dapat diedit.'
                );
        }

        $validated = $request->validate([
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')
                    ->where('is_active', true),
            ],

            'quantity' => 'required|numeric|min:0.001',

            'unit_cost' => 'required|numeric|min:0',

            'discount' => 'nullable|numeric|min:0',

            'received_quantity' => 'nullable|numeric|min:0',

            'notes' => 'nullable|string',
        ]);

        $quantity = (float) $validated['quantity'];
        $unitCost = (float) $validated['unit_cost'];
        $discount = (float) ($validated['discount'] ?? 0);

        $subtotal = ($quantity * $unitCost) - $discount;

        if ($subtotal < 0) {
            return back()
                ->withErrors([
                    'discount' =>
                        'Diskon tidak boleh lebih besar dari nilai item.',
                ])
                ->withInput();
        }

        if (
            isset($validated['received_quantity'])
            && $validated['received_quantity'] > $quantity
        ) {
            return back()
                ->withErrors([
                    'received_quantity' =>
                        'Quantity diterima tidak boleh lebih besar dari quantity pembelian.',
                ])
                ->withInput();
        }

        $validated['discount'] = $discount;
        $validated['subtotal'] = $subtotal;
        $validated['received_quantity'] =
            $validated['received_quantity'] ?? 0;

        $purchaseId = $purchaseItem->purchase_id;

        $purchaseItem->update($validated);

        $this->recalculatePurchaseTotal(
            $purchaseId
        );

        return redirect()
            ->route(
                'purchases.show',
                $purchaseId
            )
            ->with(
                'success',
                'Item purchase berhasil diperbarui.'
            );
    }

    /**
     * Menghapus item purchase.
     */
    public function destroy(PurchaseItem $purchaseItem)
    {
        if (
            in_array(
                $purchaseItem->purchase->status,
                ['RECEIVED', 'CANCELLED']
            )
        ) {
            return redirect()
                ->route(
                    'purchases.show',
                    $purchaseItem->purchase_id
                )
                ->with(
                    'error',
                    'Item pada purchase ini tidak dapat dihapus.'
                );
        }

        $purchaseId = $purchaseItem->purchase_id;

        $purchaseItem->delete();

        $this->recalculatePurchaseTotal(
            $purchaseId
        );

        return redirect()
            ->route(
                'purchases.show',
                $purchaseId
            )
            ->with(
                'success',
                'Item purchase berhasil dihapus.'
            );
    }

    /**
     * Menghitung ulang total purchase berdasarkan item.
     */
    private function recalculatePurchaseTotal(
        int $purchaseId
    ): void {
        $purchase = Purchase::findOrFail(
            $purchaseId
        );

        $subtotal = $purchase->items()->sum(
            'subtotal'
        );

        $discount = $purchase->discount ?? 0;

        $grandTotal = max(
            0,
            $subtotal - $discount
        );

        $purchase->update([
            'subtotal' => $subtotal,
            'grand_total' => $grandTotal,
        ]);
    }
}