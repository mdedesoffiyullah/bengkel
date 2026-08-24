<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\WorkOrderItemController;
use App\Http\Controllers\WorkOrderAdditionalChargeController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseItemController;
use App\Http\Controllers\InventoryBalanceController;
use App\Http\Controllers\InventoryLayerController;
use App\Http\Controllers\StockAllocationController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\StockOpnameItemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfitLossController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\ActivityLogController;

Route::get('/', fn () => view('dashboard'))->name('dashboard');
Route::resource('customers', CustomerController::class);
Route::resource('vehicles', VehicleController::class);
Route::resource('product-categories', ProductCategoryController::class);
Route::resource('products', ProductController::class);
Route::resource('suppliers', SupplierController::class);
Route::resource('services', ServiceController::class);
Route::resource('work-orders', WorkOrderController::class);

Route::get('/work-orders/{workOrder}/print-direct', function (\App\Models\WorkOrder $workOrder) {
    $workOrder->load(['customer', 'items.product', 'items.service', 'payments']);

    $money = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $qty = fn ($value) => rtrim(rtrim(number_format((float) $value, 3, ',', '.'), '0'), ',');
    $line = str_repeat('-', 32);

    $text = "\x1B@";
    $text .= "\x1B!\x08";
    $text .= "BENGKEL\n";
    $text .= "MANAGEMENT SYSTEM\n";
    $text .= "\x1B!\x00";
    $text .= $line . "\n";
    $text .= "NO : {$workOrder->code}\n";
    $text .= "TGL: " . optional($workOrder->opened_at)->format('d/m/Y H:i') . "\n";
    $text .= $line . "\n";
    $text .= "CUSTOMER\n";
    $text .= ($workOrder->customer->name ?? '-') . "\n";
    $text .= "TELP: " . ($workOrder->customer->phone ?? '-') . "\n";
    $text .= "NOPOL: " . ($workOrder->customer->plate_number ?? '-') . "\n";
    $text .= "KEND: " . (trim(($workOrder->customer->brand ?? '') . ' ' . ($workOrder->customer->type ?? '')) ?: '-') . "\n";
    $text .= $line . "\n";
    $text .= "PEKERJAAN / SPAREPART\n";

    foreach ($workOrder->items as $item) {
        $name = $item->item_name ?? $item->product?->name ?? $item->service?->name ?? '-';
        $text .= $name . "\n";
        $text .= $qty($item->quantity) . " x " . $money($item->unit_price) . " = " . $money($item->subtotal) . "\n";
    }

    $totalPaid = (float) ($workOrder->payments?->sum('amount') ?? 0);
    $grandTotal = (float) ($workOrder->grand_total ?? 0);
    $remaining = max(0, $grandTotal - $totalPaid);

    $text .= $line . "\n";
    $text .= "Subtotal : " . $money($workOrder->subtotal) . "\n";
    $text .= "Discount : " . $money($workOrder->discount) . "\n";
    $text .= "TOTAL    : " . $money($grandTotal) . "\n";
    $text .= $line . "\n";
    $text .= "Dibayar  : " . $money($totalPaid) . "\n";
    $text .= "Sisa     : " . $money($remaining) . "\n";
    $text .= $line . "\n";
    $text .= "TERIMA KASIH\n";
    $text .= "ATAS KEPERCAYAAN ANDA\n\n\n";
    $text .= "\x1DV\x00";

    $encoded = base64_encode($text);
    $printer = 'SUPERISC S31';
    $script = '$b=[Convert]::FromBase64String(\'' . $encoded . '\'); $s=[Text.Encoding]::Default.GetString($b); $s | Out-Printer -Name \'' . $printer . '\'';
    $command = 'powershell.exe -NoProfile -NonInteractive -ExecutionPolicy Bypass -Command ' . escapeshellarg($script);
    $output = [];
    $exitCode = 0;
    exec($command . ' 2>&1', $output, $exitCode);

    if ($exitCode !== 0) {
        return response("Gagal mengirim nota ke printer {$printer}.\n" . implode("\n", $output), 500);
    }

    return response("Nota {$workOrder->code} dikirim ke printer {$printer}.");
})->name('work-orders.print-direct');

Route::get('/work-orders/{workOrder}/print', function (\App\Models\WorkOrder $workOrder) {
    $workOrder->load(['customer', 'items.product', 'items.service', 'items.supplier', 'payments']);
    return view('work_orders.print', compact('workOrder'));
})->name('work-orders.print');
Route::patch('/work-orders/{workOrder}/final', [WorkOrderController::class, 'final'])->name('work-orders.final');
Route::resource('work-order-items', WorkOrderItemController::class);
Route::resource('work-order-additional-charges', WorkOrderAdditionalChargeController::class);
Route::resource('purchases', PurchaseController::class);
Route::resource('purchase-items', PurchaseItemController::class);
Route::resource('inventory-balances', InventoryBalanceController::class);
Route::resource('inventory-layers', InventoryLayerController::class);
Route::resource('stock-allocations', StockAllocationController::class);
Route::resource('stock-movements', StockMovementController::class);
Route::post('stock-opnames/{stockOpname}/items', [StockOpnameController::class, 'addItem'])->name('stock-opnames.items.store');
Route::patch('stock-opnames/{stockOpname}/post', [StockOpnameController::class, 'post'])->name('stock-opnames.post');
Route::resource('stock-opnames', StockOpnameController::class);
Route::resource('stock-opname-items', StockOpnameItemController::class);
Route::resource('payments', PaymentController::class);
Route::get('profit-loss', [ProfitLossController::class, 'index'])->name('profit-loss.index');
Route::resource('invoices', InvoiceController::class);
Route::resource('refunds', RefundController::class);
Route::resource('expenses', ExpenseController::class);
Route::resource('complaints', ComplaintController::class);
Route::resource('activity-logs', ActivityLogController::class);
