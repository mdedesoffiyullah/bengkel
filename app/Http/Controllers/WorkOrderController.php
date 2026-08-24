<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Service;
use App\Models\Supplier;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use App\Services\InventoryFifoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Str;
use Illuminate\Validation\Rule;

class WorkOrderController extends Controller
{
    public function index()
    {
        $workOrders = WorkOrder::with('customer')->latest()->paginate(15);
        return view('work_orders.index', compact('workOrders'));
    }

    public function create()
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $services = Service::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $nextCode = $this->generateCode('WO-', 'work_orders', 'code');

        return view('work_orders.create', compact(
            'customers', 'products', 'services', 'suppliers', 'nextCode'
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validateWorkOrder($request);

        return DB::transaction(function () use ($request, $validated) {
            $customer = $this->resolveCustomer($validated);

            $workOrder = WorkOrder::create([
                'code' => $validated['code'],
                'status' => $validated['status'] ?? 'OPEN',
                'customer_id' => $customer?->id,
                'type' => $validated['type'] ?? 'REGULAR',
                'opened_at' => $validated['opened_at'] ?? now(),
                'complaint' => $validated['complaint'] ?? null,
                'diagnosis' => $validated['diagnosis'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'discount' => $validated['discount'] ?? 0,
                'subtotal' => 0,
                'grand_total' => 0,
            ]);

            $subtotal = $this->saveItems($workOrder, $request->input('items', []));
            $discount = (float) ($validated['discount'] ?? 0);
            $grandTotal = max(0, $subtotal - $discount);

            $workOrder->update([
                'subtotal' => $subtotal,
                'grand_total' => $grandTotal,
            ]);

            $paymentAmount = (float) ($validated['payment_amount'] ?? 0);
            if ($paymentAmount > 0) {
                if ($paymentAmount > $grandTotal) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'payment_amount' => 'Jumlah pembayaran tidak boleh melebihi Grand Total Work Order.',
                    ]);
                }

                $paymentMethod = $validated['payment_method'] ?? null;
                if (!$paymentMethod) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'payment_method' => 'Metode pembayaran wajib dipilih jika ada pembayaran.',
                    ]);
                }

                $workOrder->payments()->create([
                    'code' => 'PAY-' . now()->format('YmdHisv'),
                    'transaction_type' => 'CUSTOMER_PAYMENT',
                    'paid_at' => $validated['payment_paid_at'] ?? now(),
                    'amount' => $paymentAmount,
                    'method' => $paymentMethod,
                    'reference_number' => $validated['payment_reference_number'] ?? null,
                    'notes' => $validated['payment_notes'] ?? null,
                ]);
            }

            return redirect()->route('work-orders.show', $workOrder)->with(
                'success',
                $paymentAmount > 0 ? 'Work Order dan pembayaran berhasil dibuat.' : 'Work Order berhasil dibuat.'
            );
        });
    }

    public function show(WorkOrder $workOrder)
    {
        $workOrder->load([
            'customer',
            'items.product.category',
            'items.service',
            'items.supplier',
            'additionalCharges',
            'payments',
        ]);

        return view('work_orders.show', compact('workOrder'));
    }

    public function edit(WorkOrder $workOrder)
    {
        if ($workOrder->status === 'COMPLETED') {
            return redirect()->route('work-orders.show', $workOrder)->with('error', 'Work Order yang sudah FINAL tidak dapat diedit.');
        }

        $workOrder->load(['customer', 'items.product.category', 'items.service', 'items.supplier']);
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $services = Service::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('work_orders.edit', compact('workOrder', 'customers', 'products', 'services', 'suppliers'));
    }

    public function update(Request $request, WorkOrder $workOrder)
    {
        if ($workOrder->status === 'COMPLETED') {
            return redirect()->route('work-orders.show', $workOrder)->with('error', 'Work Order yang sudah FINAL tidak dapat diedit.');
        }

        $validated = $this->validateWorkOrder($request, $workOrder);

        return DB::transaction(function () use ($request, $validated, $workOrder) {
            $customer = $this->resolveCustomer($validated);
            $totalPaidBefore = (float) $workOrder->payments()->sum('amount');

            $workOrder->update([
                'code' => $validated['code'],
                'status' => $validated['status'] ?? $workOrder->status,
                'customer_id' => $customer?->id,
                'type' => $validated['type'] ?? 'REGULAR',
                'opened_at' => $validated['opened_at'] ?? $workOrder->opened_at,
                'complaint' => $validated['complaint'] ?? null,
                'diagnosis' => $validated['diagnosis'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'discount' => $validated['discount'] ?? 0,
            ]);

            $workOrder->items()->delete();
            $subtotal = $this->saveItems($workOrder, $request->input('items', []));
            $discount = (float) ($validated['discount'] ?? 0);
            $grandTotal = max(0, $subtotal - $discount);

            $workOrder->update(['subtotal' => $subtotal, 'grand_total' => $grandTotal]);

            $remainingBeforeNewPayment = max(0, $grandTotal - $totalPaidBefore);
            $paymentAmount = (float) ($validated['payment_amount'] ?? 0);

            if ($paymentAmount > 0) {
                if ($paymentAmount > $remainingBeforeNewPayment) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'payment_amount' => 'Jumlah pembayaran melebihi sisa tagihan Work Order. Sisa tagihan saat ini: Rp ' . number_format($remainingBeforeNewPayment, 0, ',', '.'),
                    ]);
                }

                $paymentMethod = $validated['payment_method'] ?? null;
                if (!$paymentMethod) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'payment_method' => 'Metode pembayaran wajib dipilih jika ada pembayaran.',
                    ]);
                }

                $workOrder->payments()->create([
                    'code' => 'PAY-' . now()->format('YmdHisv'),
                    'transaction_type' => 'CUSTOMER_PAYMENT',
                    'paid_at' => $validated['payment_paid_at'] ?? now(),
                    'amount' => $paymentAmount,
                    'method' => $paymentMethod,
                    'reference_number' => $validated['payment_reference_number'] ?? null,
                    'notes' => $validated['payment_notes'] ?? null,
                ]);
            }

            $totalPaidAfter = (float) $workOrder->payments()->sum('amount');
            $remainingAfterPayment = max(0, $grandTotal - $totalPaidAfter);

            $message = $remainingAfterPayment <= 0
                ? 'Work Order berhasil diperbarui dan pembayaran sudah LUNAS.'
                : ($paymentAmount > 0
                    ? 'Work Order dan pembayaran berhasil diperbarui. Sisa tagihan: Rp ' . number_format($remainingAfterPayment, 0, ',', '.')
                    : 'Work Order berhasil diperbarui. Sisa tagihan: Rp ' . number_format($remainingAfterPayment, 0, ',', '.'));

            return redirect()->route('work-orders.show', $workOrder)->with('success', $message);
        });
    }

    public function destroy(WorkOrder $workOrder)
    {
        if ($workOrder->status === 'COMPLETED') {
            return redirect()->route('work-orders.index')->with('error', 'Work Order yang sudah FINAL tidak dapat dihapus.');
        }

        $workOrder->delete();
        return redirect()->route('work-orders.index')->with('success', 'Work Order berhasil dihapus.');
    }

    public function final(WorkOrder $workOrder)
    {
        if ($workOrder->status === 'COMPLETED') {
            return redirect()->route('work-orders.show', $workOrder)->with('error', 'Work Order sudah FINAL.');
        }

        $workOrder->update(['status' => 'COMPLETED', 'completed_at' => now()]);
        return redirect()->route('work-orders.show', $workOrder)->with('success', 'Work Order berhasil di-FINAL.');
    }

    private function validateWorkOrder(Request $request, ?WorkOrder $workOrder = null): array
    {
        $items = $request->input('items', []);
        foreach ($items as $index => $item) {
            foreach (['purchase_quantity', 'wo_quantity', 'remaining_quantity'] as $quantityField) {
                if (array_key_exists($quantityField, $item)) {
                    $value = trim((string) $item[$quantityField]);
                    $items[$index][$quantityField] = $value === '' ? null : (int) $value;
                }
            }
        }
        $request->merge(['items' => $items]);

        return $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('work_orders', 'code')->ignore($workOrder?->id)],
            'status' => ['nullable', Rule::in(['OPEN', 'IN_PROGRESS', 'WAITING_PARTS', 'COMPLETED', 'CANCELLED'])],
            'type' => ['required', Rule::in(['REGULAR', 'WARRANTY'])],
            'opened_at' => 'nullable|date',
            'complaint' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'notes' => 'nullable|string',
            'discount' => 'nullable|numeric|min:0',

            'customer_mode' => ['required', Rule::in(['EXISTING', 'NEW'])],
            'customer_id' => ['required_if:customer_mode,EXISTING', 'nullable', 'integer', 'exists:customers,id'],
            'customer_code' => 'nullable|string|max:30',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:30',
            'customer_plate_number' => 'nullable|string|max:30',
            'customer_brand' => 'nullable|string|max:100',
            'customer_type' => 'nullable|string|max:100',
            'customer_notes' => 'nullable|string',

            'payment_amount' => 'nullable|numeric|gt:0',
            'payment_paid_at' => 'nullable|date',
            'payment_method' => ['nullable', Rule::in(['CASH', 'BANK_TRANSFER', 'DEBIT_CARD', 'CREDIT_CARD', 'QRIS', 'OTHER'])],
            'payment_reference_number' => 'nullable|string|max:100',
            'payment_notes' => 'nullable|string',

            'items' => 'nullable|array',
            'items.*.item_type' => ['nullable', Rule::in(['SERVICE', 'PRODUCT'])],
            'items.*.mode' => ['nullable', Rule::in(['EXISTING', 'NEW'])],
            'items.*.service_id' => 'nullable|integer|exists:services,id',
            'items.*.product_id' => 'nullable|integer|exists:products,id',
            'items.*.supplier_id' => 'nullable|integer|exists:suppliers,id',
            'items.*.service_code' => 'nullable|string|max:50',
            'items.*.service_name' => 'nullable|string|max:255',
            'items.*.service_default_price' => 'nullable|numeric|min:0',
            'items.*.service_description' => 'nullable|string',
            'items.*.service_estimated_duration' => 'nullable|integer|min:0',
            'items.*.product_code' => 'nullable|string|max:50',
            'items.*.product_category_name' => 'nullable|string|max:255',
            'items.*.product_barcode' => 'nullable|string|max:100',
            'items.*.product_name' => 'nullable|string|max:255',
            'items.*.product_brand' => 'nullable|string|max:100',
            'items.*.product_unit' => 'nullable|string|max:20',
            'items.*.product_stock_type' => 'nullable|in:STOCK,NON_STOCK',
            'items.*.product_purchase_price' => 'nullable|numeric|min:0',
            'items.*.product_selling_price' => 'nullable|numeric|min:0',
            'items.*.product_minimum_stock' => 'nullable|numeric|min:0',
            'items.*.quantity' => 'nullable|integer|min:1',
            'items.*.purchase_quantity' => 'nullable|integer|min:0',
            'items.*.wo_quantity' => 'nullable|integer|min:0',
            'items.*.remaining_quantity' => 'nullable|integer|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);
    }

    private function resolveCustomer(array $validated): ?Customer
    {
        if (($validated['customer_mode'] ?? 'EXISTING') === 'EXISTING') {
            $customerId = $validated['customer_id'] ?? null;
            if (!$customerId) abort(422, 'Silakan pilih Customer terlebih dahulu.');
            $customer = Customer::find($customerId);
            if (!$customer) abort(422, 'Customer yang dipilih tidak ditemukan.');
            return $customer;
        }

        $name = trim($validated['customer_name'] ?? '');
        if ($name === '') return null;
        $code = trim($validated['customer_code'] ?? '') ?: $this->generateCode('CUS-', 'customers', 'code');

        return Customer::create([
            'code' => $code,
            'name' => $name,
            'phone' => trim($validated['customer_phone'] ?? '') ?: null,
            'plate_number' => strtoupper(trim($validated['customer_plate_number'] ?? '')) ?: null,
            'brand' => trim($validated['customer_brand'] ?? '') ?: null,
            'type' => trim($validated['customer_type'] ?? '') ?: null,
            'notes' => trim($validated['customer_notes'] ?? '') ?: null,
            'is_active' => true,
        ]);
    }

    private function saveItems(WorkOrder $workOrder, array $items): float
    {
        $subtotal = 0;

        foreach ($items as $item) {
            $woQuantity = (float) ($item['wo_quantity'] ?? 0);
            if ($woQuantity <= 0) continue;

            $quantity = $woQuantity;
            $itemType = $item['item_type'] ?? 'SERVICE';
            $mode = $item['mode'] ?? 'EXISTING';
            $serviceId = null;
            $productId = null;
            $supplierId = null;
            $itemCode = null;
            $itemName = null;
            $unit = 'JASA';
            $unitPrice = 0;
            $unitCost = 0;

            if ($itemType === 'SERVICE') {
                if ($mode === 'NEW') {
                    $serviceCode = trim($item['service_code'] ?? '') ?: $this->generateCode('JS-', 'services', 'code');
                    $serviceName = trim($item['service_name'] ?? '');
                    if ($serviceName === '') continue;

                    $service = Service::firstOrCreate(['code' => $serviceCode], [
                        'name' => $serviceName,
                        'description' => trim($item['service_description'] ?? '') ?: null,
                        'default_price' => (float) ($item['service_default_price'] ?? 0),
                        'estimated_duration' => (int) ($item['service_estimated_duration'] ?? 0),
                        'is_active' => true,
                    ]);
                    $service->update([
                        'name' => $serviceName,
                        'description' => trim($item['service_description'] ?? '') ?: null,
                        'default_price' => (float) ($item['service_default_price'] ?? 0),
                        'estimated_duration' => (int) ($item['service_estimated_duration'] ?? 0),
                        'is_active' => true,
                    ]);
                } else {
                    $service = Service::find($item['service_id'] ?? null);
                    if (!$service) continue;
                }

                $serviceId = $service->id;
                $itemCode = $service->code;
                $itemName = $service->name;
                $unit = 'JASA';
                $unitPrice = (float) $service->default_price;
            } else {
                if ($mode === 'NEW') {
                    $productCode = trim($item['product_code'] ?? '') ?: $this->generateCode('SP-', 'products', 'code');
                    $productName = trim($item['product_name'] ?? '');
                    if ($productName === '') continue;

                    $categoryName = trim($item['product_category_name'] ?? '');
                    $category = $categoryName !== ''
                        ? ProductCategory::firstOrCreate(['name' => $categoryName], ['code' => $this->generateCategoryCode($categoryName), 'is_active' => true])
                        : ProductCategory::firstOrCreate(['code' => 'SPAREPART-MANUAL'], ['name' => 'Sparepart Manual', 'description' => 'Kategori default sparepart dari Work Order.', 'is_active' => true]);

                    $product = Product::firstOrCreate(['code' => $productCode], [
                        'category_id' => $category->id,
                        'barcode' => trim($item['product_barcode'] ?? '') ?: null,
                        'name' => $productName,
                        'brand' => trim($item['product_brand'] ?? '') ?: null,
                        'unit' => trim($item['product_unit'] ?? 'PCS') ?: 'PCS',
                        'stock_type' => $item['product_stock_type'] ?? 'STOCK',
                        'last_buy_price' => (float) ($item['product_purchase_price'] ?? 0),
                        'selling_price' => (float) ($item['product_selling_price'] ?? 0),
                        'minimum_stock' => (float) ($item['product_minimum_stock'] ?? 0),
                        'is_active' => true,
                    ]);
                    $product->update([
                        'category_id' => $category->id,
                        'barcode' => trim($item['product_barcode'] ?? '') ?: null,
                        'name' => $productName,
                        'brand' => trim($item['product_brand'] ?? '') ?: null,
                        'unit' => trim($item['product_unit'] ?? 'PCS') ?: 'PCS',
                        'stock_type' => $item['product_stock_type'] ?? 'STOCK',
                        'last_buy_price' => (float) ($item['product_purchase_price'] ?? 0),
                        'selling_price' => (float) ($item['product_selling_price'] ?? 0),
                        'minimum_stock' => (float) ($item['product_minimum_stock'] ?? 0),
                        'is_active' => true,
                    ]);
                } else {
                    $product = Product::find($item['product_id'] ?? null);
                    if (!$product) continue;
                }

                $productId = $product->id;
                $supplierId = !empty($item['supplier_id']) ? (int) $item['supplier_id'] : null;
                $itemCode = $product->code;
                $itemName = $product->name;
                $unit = $product->unit ?: 'PCS';
                $unitPrice = (float) $product->selling_price;
                $unitCost = (float) $product->last_buy_price;
            }

            $discountAmount = (float) ($item['discount_amount'] ?? 0);
            $lineSubtotal = max(0, ($quantity * $unitPrice) - $discountAmount);
            $totalCost = $quantity * $unitCost;
            $purchaseQuantity = (int) ($item['purchase_quantity'] ?? 0);
            $remainingQuantity = max(0, $purchaseQuantity - $woQuantity);

            $workOrderItem = WorkOrderItem::create([
                'work_order_id' => $workOrder->id,
                'item_type' => $itemType,
                'service_id' => $serviceId,
                'product_id' => $productId,
                'supplier_id' => $supplierId,
                'item_code' => $itemCode,
                'item_name' => $itemName,
                'unit' => $unit,
                'quantity' => $quantity,
                'purchase_quantity' => $purchaseQuantity,
                'wo_quantity' => $woQuantity,
                'remaining_quantity' => $remainingQuantity,
                'unit_price' => $unitPrice,
                'discount_amount' => $discountAmount,
                'subtotal' => $lineSubtotal,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'status' => 'PENDING',
                'notes' => $item['notes'] ?? null,
            ]);

            if ($itemType === 'PRODUCT' && $productId && $woQuantity > 0 && $purchaseQuantity <= 0) {
                $this->consumeInventory($product, (int) $woQuantity, $workOrder, $workOrderItem->id);
            }

            $subtotal += $lineSubtotal;
        }

        return $subtotal;
    }

    private function consumeInventory(Product $product, int $quantity, WorkOrder $workOrder, ?int $workOrderItemId = null): void
    {
        app(InventoryFifoService::class)->consumeForWorkOrder($workOrder, $product->id, $quantity, $workOrderItemId);
    }

    private function generateCode(string $prefix, string $table, string $column): string
    {
        do {
            $code = $prefix . strtoupper(Str::random(8));
        } while (DB::table($table)->where($column, $code)->exists());
        return $code;
    }

    private function generateCategoryCode(string $name): string
    {
        $base = substr(strtoupper(Str::slug($name, '-')), 0, 15);
        if ($base === '') $base = 'CATEGORY';
        $code = $base;
        $counter = 1;
        while (ProductCategory::where('code', $code)->exists()) {
            $code = $base . '-' . $counter++;
        }
        return $code;
    }
}
