<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\WorkOrder;
use App\Services\InventoryFifoService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * Menampilkan daftar purchase.
     */
    public function index()
    {
        $purchases = Purchase::with([
            'supplier',
            'workOrder',
        ])
            ->latest()
            ->paginate(10);

        return view('purchases.index', compact('purchases'));
    }

    /**
     * Form membuat purchase.
     */
    public function create()
    {
        $suppliers = Supplier::where('is_active', true)
            ->orderBy('name')
            ->get();

        $workOrders = WorkOrder::whereNotIn('status', [
            'COMPLETED',
            'CANCELLED',
        ])
            ->latest()
            ->get();

        return view(
            'purchases.create',
            compact('suppliers', 'workOrders')
        );
    }

    /**
     * Menyimpan purchase baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:30|unique:purchases,code',

            'supplier_id' => [
                'required',
                'integer',
                'exists:suppliers,id',
            ],

            'work_order_id' => [
                'nullable',
                'integer',
                'exists:work_orders,id',
            ],

            'status' => [
                'nullable',
                Rule::in([
                    'DRAFT',
                    'ORDERED',
                    'PARTIAL',
                    'RECEIVED',
                    'CANCELLED',
                ]),
            ],

            'purchase_type' => [
                'required',
                Rule::in([
                    'GENERAL',
                    'WO',
                ]),
            ],

            'purchase_date' => 'required|date',

            'received_at' => 'nullable|date',

            'subtotal' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'grand_total' => 'nullable|numeric|min:0',

            'notes' => 'nullable|string',
        ]);

        /*
         * Purchase dengan tipe WO harus mempunyai WO.
         */
        if (
            $validated['purchase_type'] === 'WO'
            && empty($validated['work_order_id'])
        ) {
            return back()
                ->withErrors([
                    'work_order_id' =>
                        'Purchase tipe WO wajib memiliki Work Order.',
                ])
                ->withInput();
        }

        /*
         * Purchase GENERAL tidak harus memiliki WO.
         */
        if (
            $validated['purchase_type'] === 'GENERAL'
        ) {
            $validated['work_order_id'] = null;
        }

        $validated['status'] =
            $validated['status'] ?? 'DRAFT';

        $validated['subtotal'] =
            $validated['subtotal'] ?? 0;

        $validated['discount'] =
            $validated['discount'] ?? 0;

        $validated['grand_total'] =
            $validated['grand_total'] ?? 0;

        Purchase::create($validated);

        return redirect()
            ->route('purchases.index')
            ->with(
                'success',
                'Purchase berhasil dibuat.'
            );
    }

    /**
     * Menampilkan detail purchase.
     */
    public function show(Purchase $purchase)
    {
        $purchase->load([
            'supplier',
            'workOrder',
            'items.product',
        ]);

        return view(
            'purchases.show',
            compact('purchase')
        );
    }

    /**
     * Form edit purchase.
     */
    public function edit(Purchase $purchase)
    {
        /*
         * Purchase yang sudah RECEIVED tidak boleh diedit
         * melalui CRUD biasa karena sudah berhubungan
         * dengan inventory.
         */
        if ($purchase->status === 'RECEIVED') {
            return redirect()
                ->route('purchases.show', $purchase)
                ->with(
                    'error',
                    'Purchase yang sudah diterima tidak dapat diedit.'
                );
        }

        $suppliers = Supplier::where('is_active', true)
            ->orderBy('name')
            ->get();

        $workOrders = WorkOrder::whereNotIn('status', [
            'COMPLETED',
            'CANCELLED',
        ])
            ->latest()
            ->get();

        return view(
            'purchases.edit',
            compact(
                'purchase',
                'suppliers',
                'workOrders'
            )
        );
    }

    /**
     * Memperbarui purchase.
     */
    public function update(
        Request $request,
        Purchase $purchase
    ) {
        if ($purchase->status === 'RECEIVED') {
            return redirect()
                ->route('purchases.show', $purchase)
                ->with(
                    'error',
                    'Purchase yang sudah diterima tidak dapat diedit.'
                );
        }

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('purchases', 'code')
                    ->ignore($purchase->id),
            ],

            'supplier_id' => [
                'required',
                'integer',
                'exists:suppliers,id',
            ],

            'work_order_id' => [
                'nullable',
                'integer',
                'exists:work_orders,id',
            ],

            'status' => [
                'required',
                Rule::in([
                    'DRAFT',
                    'ORDERED',
                    'PARTIAL',
                    'RECEIVED',
                    'CANCELLED',
                ]),
            ],

            'purchase_type' => [
                'required',
                Rule::in([
                    'GENERAL',
                    'WO',
                ]),
            ],

            'purchase_date' => 'required|date',

            'received_at' => 'nullable|date',

            'subtotal' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'grand_total' => 'nullable|numeric|min:0',

            'notes' => 'nullable|string',
        ]);

        if (
            $validated['purchase_type'] === 'WO'
            && empty($validated['work_order_id'])
        ) {
            return back()
                ->withErrors([
                    'work_order_id' =>
                        'Purchase tipe WO wajib memiliki Work Order.',
                ])
                ->withInput();
        }

        if (
            $validated['purchase_type'] === 'GENERAL'
        ) {
            $validated['work_order_id'] = null;
        }

        /*
         * =====================================================
         * PURCHASE RECEIVED
         * =====================================================
         *
         * Contoh:
         *
         * Purchase Item = 25
         * received      = 0
         *
         * status RECEIVED
         *      ?
         * Stock Movement PURCHASE = 25
         *      ?
         * Inventory Layer = 25
         *      ?
         * Inventory Balance = +25
         *
         * Jika sebelumnya sudah menerima sebagian,
         * hanya sisa yang masuk inventory.
         */
        if ($validated['status'] === 'RECEIVED') {

            DB::transaction(function () use (
                $purchase,
                $validated
            ) {
                $purchase->load('items');

                $fifo = app(InventoryFifoService::class);

                foreach ($purchase->items as $item) {

                    /*
                     * received_quantity = jumlah barang yang benar-benar
                     * diterima dari supplier dan masuk inventory.
                     *
                     * JANGAN memakai:
                     *
                     *     quantity - received_quantity
                     *
                     * karena itu menghasilkan 0 ketika seluruh barang
                     * sudah diterima.
                     */
                    $received =
                        (int) $item->received_quantity;

                    if ($received > 0) {
                        $fifo->receivePurchaseItem(
                            $item,
                            $received,
                            $validated['received_at'] ?? now()
                        );
                    }
                }

                $purchase->update([
                    'status' => 'RECEIVED',
                    'received_at' =>
                        $validated['received_at'] ?? now(),
                    'code' =>
                        $validated['code'],
                    'supplier_id' =>
                        $validated['supplier_id'],
                    'work_order_id' =>
                        $validated['work_order_id'],
                    'purchase_type' =>
                        $validated['purchase_type'],
                    'purchase_date' =>
                        $validated['purchase_date'],
                    'subtotal' =>
                        $validated['subtotal'] ?? 0,
                    'discount' =>
                        $validated['discount'] ?? 0,
                    'grand_total' =>
                        $validated['grand_total'] ?? 0,
                    'notes' =>
                        $validated['notes'] ?? null,
                ]);
            });

        } else {

            $purchase->update($validated);
        }

        return redirect()
            ->route('purchases.show', $purchase)
            ->with(
                'success',
                'Purchase berhasil diperbarui.'
            );
    }

    /**
     * Menghapus purchase.
     */
    public function destroy(Purchase $purchase)
    {
        if ($purchase->status === 'RECEIVED') {
            return redirect()
                ->route('purchases.show', $purchase)
                ->with(
                    'error',
                    'Purchase yang sudah diterima tidak dapat dihapus.'
                );
        }

        $purchase->delete();

        return redirect()
            ->route('purchases.index')
            ->with(
                'success',
                'Purchase berhasil dihapus.'
            );
    }
}


