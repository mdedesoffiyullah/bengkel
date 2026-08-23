<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Service;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorkOrderItemController extends Controller
{
    /**
     * Menampilkan item Work Order.
     */
    public function index(Request $request)
    {
        $query = WorkOrderItem::with([
            'workOrder',
            'product',
            'service',
        ]);

        if ($request->filled('work_order_id')) {
            $query->where(
                'work_order_id',
                $request->work_order_id
            );
        }

        $items = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view(
            'work_order_items.index',
            compact('items')
        );
    }

    /**
     * Form tambah item.
     */
    public function create(Request $request)
    {
        $workOrder = null;

        if ($request->filled('work_order_id')) {
            $workOrder = WorkOrder::findOrFail(
                $request->work_order_id
            );
        }

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        $services = Service::where('is_active', true)
            ->orderBy('name')
            ->get();

        $workOrders = WorkOrder::whereNotIn('status', [
            'COMPLETED',
            'CANCELLED',
        ])
            ->latest()
            ->get();

        return view(
            'work_order_items.create',
            compact(
                'workOrder',
                'workOrders',
                'products',
                'services'
            )
        );
    }

    /**
     * Menyimpan item baru.
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

            'item_type' => [
                'required',
                Rule::in([
                    'PRODUCT',
                    'SERVICE',
                ]),
            ],

            'product_id' => [
                'nullable',
                'integer',
                'required_if:item_type,PRODUCT',
                'exists:products,id',
            ],

            'service_id' => [
                'nullable',
                'integer',
                'required_if:item_type,SERVICE',
                'exists:services,id',
            ],

            'description' => 'nullable|string',

            'quantity' => 'required|numeric|min:0.001',

            'unit_price' => 'required|numeric|min:0',

            'unit_cost' => 'nullable|numeric|min:0',

            'discount' => 'nullable|numeric|min:0',

            'notes' => 'nullable|string',
        ]);

        $quantity = (float) $validated['quantity'];
        $unitPrice = (float) $validated['unit_price'];
        $discount = (float) ($validated['discount'] ?? 0);

        $subtotal = ($quantity * $unitPrice) - $discount;

        if ($subtotal < 0) {
            return back()
                ->withErrors([
                    'discount' => 'Diskon tidak boleh lebih besar dari nilai item.',
                ])
                ->withInput();
        }

        $validated['subtotal'] = $subtotal;
        $validated['discount'] = $discount;
        $validated['unit_cost'] =
            $validated['unit_cost'] ?? 0;

        WorkOrderItem::create($validated);

        return redirect()
            ->route(
                'work-orders.show',
                $validated['work_order_id']
            )
            ->with(
                'success',
                'Item Work Order berhasil ditambahkan.'
            );
    }

    /**
     * Menampilkan detail item.
     */
    public function show(WorkOrderItem $workOrderItem)
    {
        $workOrderItem->load([
            'workOrder',
            'product',
            'service',
        ]);

        return view(
            'work_order_items.show',
            compact('workOrderItem')
        );
    }

    /**
     * Form edit item.
     */
    public function edit(WorkOrderItem $workOrderItem)
    {
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        $services = Service::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'work_order_items.edit',
            compact(
                'workOrderItem',
                'products',
                'services'
            )
        );
    }

    /**
     * Memperbarui item.
     */
    public function update(
        Request $request,
        WorkOrderItem $workOrderItem
    ) {
        $validated = $request->validate([
            'item_type' => [
                'required',
                Rule::in([
                    'PRODUCT',
                    'SERVICE',
                ]),
            ],

            'product_id' => [
                'nullable',
                'integer',
                'required_if:item_type,PRODUCT',
                'exists:products,id',
            ],

            'service_id' => [
                'nullable',
                'integer',
                'required_if:item_type,SERVICE',
                'exists:services,id',
            ],

            'description' => 'nullable|string',

            'quantity' => 'required|numeric|min:0.001',

            'unit_price' => 'required|numeric|min:0',

            'unit_cost' => 'nullable|numeric|min:0',

            'discount' => 'nullable|numeric|min:0',

            'notes' => 'nullable|string',
        ]);

        $quantity = (float) $validated['quantity'];
        $unitPrice = (float) $validated['unit_price'];
        $discount = (float) ($validated['discount'] ?? 0);

        $subtotal = ($quantity * $unitPrice) - $discount;

        if ($subtotal < 0) {
            return back()
                ->withErrors([
                    'discount' => 'Diskon tidak boleh lebih besar dari nilai item.',
                ])
                ->withInput();
        }

        $validated['subtotal'] = $subtotal;
        $validated['discount'] = $discount;
        $validated['unit_cost'] =
            $validated['unit_cost'] ?? 0;

        $workOrderItem->update($validated);

        return redirect()
            ->route(
                'work-orders.show',
                $workOrderItem->work_order_id
            )
            ->with(
                'success',
                'Item Work Order berhasil diperbarui.'
            );
    }

    /**
     * Menghapus item.
     */
    public function destroy(
        WorkOrderItem $workOrderItem
    ) {
        $workOrderId =
            $workOrderItem->work_order_id;

        $workOrderItem->delete();

        return redirect()
            ->route(
                'work-orders.show',
                $workOrderId
            )
            ->with(
                'success',
                'Item Work Order berhasil dihapus.'
            );
    }
}