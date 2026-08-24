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

    // Out-Printer uses the Windows text-printing pipeline and can strip/translate
    // ESC/POS control bytes. Send the bytes directly to the Windows spooler as RAW.
    $encoded = base64_encode($text);
    $printer = 'SUPERISC S31';

    $powershell = <<<'PS'
$raw = [Convert]::FromBase64String('__RAW__')
$printer = '__PRINTER__'

Add-Type -TypeDefinition @'
using System;
using System.ComponentModel;
using System.Runtime.InteropServices;

public static class RawPrinter {
    [StructLayout(LayoutKind.Sequential, CharSet = CharSet.Unicode)]
    private class DOCINFO {
        [MarshalAs(UnmanagedType.LPWStr)] public string pDocName;
        [MarshalAs(UnmanagedType.LPWStr)] public string pOutputFile;
        [MarshalAs(UnmanagedType.LPWStr)] public string pDataType;
    }

    [DllImport("winspool.drv", SetLastError = true, CharSet = CharSet.Unicode)]
    private static extern bool OpenPrinter(string pPrinterName, out IntPtr phPrinter, IntPtr pDefault);

    [DllImport("winspool.drv", SetLastError = true)]
    private static extern bool ClosePrinter(IntPtr hPrinter);

    [DllImport("winspool.drv", SetLastError = true, CharSet = CharSet.Unicode)]
    private static extern int StartDocPrinter(IntPtr hPrinter, int level, [In] DOCINFO di);

    [DllImport("winspool.drv", SetLastError = true)]
    private static extern bool EndDocPrinter(IntPtr hPrinter);

    [DllImport("winspool.drv", SetLastError = true)]
    private static extern bool StartPagePrinter(IntPtr hPrinter);

    [DllImport("winspool.drv", SetLastError = true)]
    private static extern bool EndPagePrinter(IntPtr hPrinter);

    [DllImport("winspool.drv", SetLastError = true)]
    private static extern bool WritePrinter(IntPtr hPrinter, byte[] pBytes, int dwCount, out int dwWritten);

    public static void Send(string printer, byte[] data) {
        IntPtr hPrinter;
        if (!OpenPrinter(printer, out hPrinter, IntPtr.Zero))
            throw new Win32Exception(Marshal.GetLastWin32Error(), "OpenPrinter gagal");

        try {
            var di = new DOCINFO {
                pDocName = "Nota Work Order",
                pDataType = "RAW",
                pOutputFile = null
            };

            if (StartDocPrinter(hPrinter, 1, di) == 0)
                throw new Win32Exception(Marshal.GetLastWin32Error(), "StartDocPrinter gagal");

            try {
                if (!StartPagePrinter(hPrinter))
                    throw new Win32Exception(Marshal.GetLastWin32Error(), "StartPagePrinter gagal");

                try {
                    int written;
                    if (!WritePrinter(hPrinter, data, data.Length, out written) || written != data.Length)
                        throw new Win32Exception(Marshal.GetLastWin32Error(), "WritePrinter gagal");
                }
                finally {
                    EndPagePrinter(hPrinter);
                }
            }
            finally {
                EndDocPrinter(hPrinter);
            }
        }
        finally {
            ClosePrinter(hPrinter);
        }
    }
}
'@

[RawPrinter]::Send($printer, $raw)
PS;

    $powershell = str_replace(
        ['__RAW__', '__PRINTER__'],
        [$encoded, $printer],
        $powershell
    );

    // -EncodedCommand avoids quoting/escaping problems with arbitrary receipt data.
    $encodedCommand = base64_encode(mb_convert_encoding($powershell, 'UTF-16LE', 'UTF-8'));
    $command = 'powershell.exe -NoProfile -NonInteractive -ExecutionPolicy Bypass -EncodedCommand ' . $encodedCommand;

    $output = [];
    $exitCode = 0;
    exec($command . ' 2>&1', $output, $exitCode);

    if ($exitCode !== 0) {
        return response(
            "Gagal mengirim nota ke printer {$printer}.\n" . implode("\n", $output),
            500
        );
    }

    return response("Nota {$workOrder->code} dikirim sebagai RAW ESC/POS ke printer {$printer}.");
})->name('work-orders.print-direct');

// Cetak Nota menggunakan jalur RAW printer Windows.
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
