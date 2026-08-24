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
    $line = str_repeat('=', 32);

    $text = "BENGKEL\r\n";
    $text .= "MANAGEMENT SYSTEM\r\n";
    $text .= $line . "\r\n";
    $text .= "NO : {$workOrder->code}\r\n";
    $text .= "TGL: " . optional($workOrder->opened_at)->format('d/m/Y H:i') . "\r\n";
    $text .= $line . "\r\n";
    $text .= "CUSTOMER\r\n";
    $text .= ($workOrder->customer->name ?? '-') . "\r\n";
    $text .= "TELP: " . ($workOrder->customer->phone ?? '-') . "\r\n";
    $text .= "NOPOL: " . ($workOrder->customer->plate_number ?? '-') . "\r\n";
    $text .= "KEND: " . (trim(($workOrder->customer->brand ?? '') . ' ' . ($workOrder->customer->type ?? '')) ?: '-') . "\r\n";
    $text .= $line . "\r\n";
    $text .= "PEKERJAAN / SPAREPART\r\n";

    foreach ($workOrder->items as $item) {
        $name = $item->item_name ?? $item->product?->name ?? $item->service?->name ?? '-';
        $text .= $name . "\r\n";
        $text .= $qty($item->quantity) . " x " . $money($item->unit_price) . " = " . $money($item->subtotal) . "\r\n";
    }

    $totalPaid = (float) ($workOrder->payments?->sum('amount') ?? 0);
    $grandTotal = (float) ($workOrder->grand_total ?? 0);
    $remaining = max(0, $grandTotal - $totalPaid);

    $text .= $line . "\r\n";
    $text .= "Subtotal : " . $money($workOrder->subtotal) . "\r\n";
    $text .= "Discount : " . $money($workOrder->discount) . "\r\n";
    $text .= "TOTAL    : " . $money($grandTotal) . "\r\n";
    $text .= $line . "\r\n";
    $text .= "Dibayar  : " . $money($totalPaid) . "\r\n";
    $text .= "Sisa     : " . $money($remaining) . "\r\n";
    $text .= $line . "\r\n";
    $text .= "TERIMA KASIH\r\n";
    $text .= "ATAS KEPERCAYAAN ANDA\r\n\r\n\r\n";

    $printer = 'SUPERISC S31';
    $encoded = base64_encode($text);

    // Kirim sebagai plain text melalui Windows Print Spooler.
    // Ini sengaja memakai jalur yang sudah terbukti bisa mencetak dari
    // PowerShell: Out-Printer -> USB002/SUPERISC S31.
    $powershell = '$ErrorActionPreference="Stop"; '
        . '$b=[Convert]::FromBase64String("' . $encoded . '"); '
        . '$s=[Text.Encoding]::UTF8.GetString($b); '
        . '$s | Out-Printer -Name "' . $printer . '"; '
        . 'Write-Output "PRINT_QUEUED"';

    $encodedCommand = base64_encode(mb_convert_encoding($powershell, 'UTF-16LE', 'UTF-8'));
    $command = 'powershell.exe -NoProfile -NonInteractive -ExecutionPolicy Bypass -EncodedCommand ' . $encodedCommand;

    $output = [];
    $exitCode = 0;
    exec($command . ' 2>&1', $output, $exitCode);

    if ($exitCode !== 0) {
        return response(
            "GAGAL MENGIRIM KE PRINT SPOOLER\nExitCode={$exitCode}\n" . implode("\n", $output),
            500,
            ['Content-Type' => 'text/plain; charset=UTF-8']
        );
    }

    return response(
        "Nota {$workOrder->code} masuk ke print spooler printer {$printer}.\n" . implode("\n", $output),
        200,
        ['Content-Type' => 'text/plain; charset=UTF-8']
    );
})->name('work-orders.print-direct');

Route::get('/work-orders/{workOrder}/print', function (\App\Models\WorkOrder $workOrder) {
    return redirect()->route('work-orders.print-direct', $workOrder);
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
