<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\WorkOrderAdditionalCharge;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorkOrderAdditionalChargeController extends Controller
{
    /**
     * Menampilkan daftar biaya tambahan.
     */
    public function index(Request $request)
    {
        $query = WorkOrderAdditionalCharge::with('workOrder');

        if ($request->filled('work_order_id')) {
            $query->where(
                'work_order_id',
                $request->work_order_id
            );
        }

        $charges = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view(
            'work_order_additional_charges.index',
            compact('charges')
        );
    }

    /**
     * Form tambah biaya tambahan.
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

        return view(
            'work_order_additional_charges.create',
            compact('workOrder', 'workOrders')
        );
    }

    /**
     * Menyimpan biaya tambahan.
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

            'code' => 'required|string|max:30|unique:work_order_additional_charges,code',

            'name' => 'required|string|max:255',

            'description' => 'nullable|string',

            'quantity' => 'required|numeric|min:0.001',

            'unit_price' => 'required|numeric|min:0',

            'unit_cost' => 'nullable|numeric|min:0',

            'status' => [
                'nullable',
                Rule::in([
                    'PENDING',
                    'CHARGED',
                    'CANCELLED',
                ]),
            ],

            'notes' => 'nullable|string',
        ]);

        $quantity = (float) $validated['quantity'];
        $unitPrice = (float) $validated['unit_price'];
        $unitCost = (float) ($validated['unit_cost'] ?? 0);

        $validated['subtotal'] =
            $quantity * $unitPrice;

        $validated['total_cost'] =
            $quantity * $unitCost;

        $validated['unit_cost'] = $unitCost;

        $validated['status'] =
            $validated['status'] ?? 'PENDING';

        WorkOrderAdditionalCharge::create($validated);

        return redirect()
            ->route(
                'work-orders.show',
                $validated['work_order_id']
            )
            ->with(
                'success',
                'Biaya tambahan berhasil ditambahkan.'
            );
    }

    /**
     * Menampilkan detail biaya tambahan.
     */
    public function show(
        WorkOrderAdditionalCharge $workOrderAdditionalCharge
    ) {
        $workOrderAdditionalCharge->load('workOrder');

        return view(
            'work_order_additional_charges.show',
            compact('workOrderAdditionalCharge')
        );
    }

    /**
     * Form edit biaya tambahan.
     */
    public function edit(
        WorkOrderAdditionalCharge $workOrderAdditionalCharge
    ) {
        $workOrders = WorkOrder::whereNotIn('status', [
            'COMPLETED',
            'CANCELLED',
        ])
            ->latest()
            ->get();

        return view(
            'work_order_additional_charges.edit',
            compact(
                'workOrderAdditionalCharge',
                'workOrders'
            )
        );
    }

    /**
     * Memperbarui biaya tambahan.
     */
    public function update(
        Request $request,
        WorkOrderAdditionalCharge $workOrderAdditionalCharge
    ) {
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

            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique(
                    'work_order_additional_charges',
                    'code'
                )->ignore(
                    $workOrderAdditionalCharge->id
                ),
            ],

            'name' => 'required|string|max:255',

            'description' => 'nullable|string',

            'quantity' => 'required|numeric|min:0.001',

            'unit_price' => 'required|numeric|min:0',

            'unit_cost' => 'nullable|numeric|min:0',

            'status' => [
                'required',
                Rule::in([
                    'PENDING',
                    'CHARGED',
                    'CANCELLED',
                ]),
            ],

            'notes' => 'nullable|string',
        ]);

        $quantity = (float) $validated['quantity'];
        $unitPrice = (float) $validated['unit_price'];
        $unitCost = (float) ($validated['unit_cost'] ?? 0);

        $validated['subtotal'] =
            $quantity * $unitPrice;

        $validated['total_cost'] =
            $quantity * $unitCost;

        $validated['unit_cost'] = $unitCost;

        $workOrderAdditionalCharge->update(
            $validated
        );

        return redirect()
            ->route(
                'work-orders.show',
                $validated['work_order_id']
            )
            ->with(
                'success',
                'Biaya tambahan berhasil diperbarui.'
            );
    }

    /**
     * Menghapus biaya tambahan.
     */
    public function destroy(
        WorkOrderAdditionalCharge $workOrderAdditionalCharge
    ) {
        $workOrderId =
            $workOrderAdditionalCharge->work_order_id;

        $workOrderAdditionalCharge->delete();

        return redirect()
            ->route(
                'work-orders.show',
                $workOrderId
            )
            ->with(
                'success',
                'Biaya tambahan berhasil dihapus.'
            );
    }
}