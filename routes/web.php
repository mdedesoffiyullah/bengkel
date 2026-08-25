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

    $printer = 'SUPERISC S31';
    $money = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $qty = fn ($value) => rtrim(rtrim(number_format((float) $value, 3, ',', '.'), '0'), ',');
    $line = str_repeat('-', 32);

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
    $text .= "ATAS KEPERCAYAAN ANDA\r\n\r\n\r\n\r\n";

    $printerEscaped = str_replace("'", "''", $printer);
    $payloadEscaped = str_replace("'", "''", $text);

    $powershell = <<<'PS'
$ErrorActionPreference = 'Stop'
$printer = '__PRINTER__'
$raw = [System.Text.Encoding]::ASCII.GetBytes('__PAYLOAD__')

function Last-Error { return [Runtime.InteropServices.Marshal]::GetLastWin32Error() }

try {
    $p = Get-Printer -Name $printer -ErrorAction Stop
    Write-Output ("PRINTER_FOUND|Name={0}|Status={1}|Driver={2}|Port={3}|WorkOffline={4}" -f $p.Name, $p.PrinterStatus, $p.DriverName, $p.PortName, $p.WorkOffline)

    Add-Type -TypeDefinition @'
using System;
using System.ComponentModel;
using System.Runtime.InteropServices;
public static class BengkelRawPrinter {
    [StructLayout(LayoutKind.Sequential, CharSet=CharSet.Unicode)]
    private class DOCINFO {
        [MarshalAs(UnmanagedType.LPWStr)] public string pDocName;
        [MarshalAs(UnmanagedType.LPWStr)] public string pOutputFile;
        [MarshalAs(UnmanagedType.LPWStr)] public string pDataType;
    }
    [DllImport("winspool.drv", SetLastError=true, CharSet=CharSet.Unicode)] private static extern bool OpenPrinter(string name, out IntPtr handle, IntPtr defaults);
    [DllImport("winspool.drv", SetLastError=true)] private static extern bool ClosePrinter(IntPtr handle);
    [DllImport("winspool.drv", SetLastError=true, CharSet=CharSet.Unicode)] private static extern int StartDocPrinter(IntPtr handle, int level, [In] DOCINFO doc);
    [DllImport("winspool.drv", SetLastError=true)] private static extern bool EndDocPrinter(IntPtr handle);
    [DllImport("winspool.drv", SetLastError=true)] private static extern bool StartPagePrinter(IntPtr handle);
    [DllImport("winspool.drv", SetLastError=true)] private static extern bool EndPagePrinter(IntPtr handle);
    [DllImport("winspool.drv", SetLastError=true)] private static extern bool WritePrinter(IntPtr handle, byte[] data, int count, out int written);

    public static void Run(string printer, byte[] data) {
        IntPtr h;
        if (!OpenPrinter(printer, out h, IntPtr.Zero)) throw new Win32Exception(Marshal.GetLastWin32Error(), "OpenPrinter gagal");
        Console.WriteLine("OPEN_PRINTER_OK");
        bool docStarted = false;
        bool pageStarted = false;
        try {
            var doc = new DOCINFO { pDocName="Bengkel Work Order", pOutputFile=null, pDataType="RAW" };
            int job = StartDocPrinter(h, 1, doc);
            Console.WriteLine("START_DOC|JobId=" + job + "|LastError=" + Marshal.GetLastWin32Error());
            if (job == 0) throw new Win32Exception(Marshal.GetLastWin32Error(), "StartDocPrinter gagal");
            docStarted = true;

            bool page = StartPagePrinter(h);
            Console.WriteLine("START_PAGE|Result=" + page + "|LastError=" + Marshal.GetLastWin32Error());
            if (!page) throw new Win32Exception(Marshal.GetLastWin32Error(), "StartPagePrinter gagal");
            pageStarted = true;

            int written;
            bool write = WritePrinter(h, data, data.Length, out written);
            int writeError = Marshal.GetLastWin32Error();
            Console.WriteLine("WRITE_PRINTER|Result=" + write + "|Bytes=" + written + "|Expected=" + data.Length + "|LastError=" + writeError);
            if (!write) throw new Win32Exception(writeError, "WritePrinter gagal");
            if (written != data.Length) throw new Exception("WritePrinter hanya menulis sebagian data.");
        }
        finally {
            if (pageStarted) {
                bool endPage = EndPagePrinter(h);
                Console.WriteLine("END_PAGE|Result=" + endPage + "|LastError=" + Marshal.GetLastWin32Error());
            }
            if (docStarted) {
                bool endDoc = EndDocPrinter(h);
                Console.WriteLine("END_DOC|Result=" + endDoc + "|LastError=" + Marshal.GetLastWin32Error());
            }
            bool closed = ClosePrinter(h);
            Console.WriteLine("CLOSE_PRINTER|Result=" + closed + "|LastError=" + Marshal.GetLastWin32Error());
        }
    }
}
'@

    [BengkelRawPrinter]::Run($printer, $raw)
    Write-Output 'RAW_PRINT_OK'
    exit 0
} catch {
    Write-Output ("RAW_PRINT_FAIL|Type={0}|Message={1}" -f $_.Exception.GetType().FullName, $_.Exception.Message)
    exit 1
}
PS;

    $powershell = str_replace(['__PRINTER__', '__PAYLOAD__'], [$printerEscaped, $payloadEscaped], $powershell);
    $scriptPath = tempnam(sys_get_temp_dir(), 'bengkel-print-') . '.ps1';
    file_put_contents($scriptPath, $powershell);

    $output = [];
    $exitCode = 0;
    $command = 'powershell.exe -NoProfile -NonInteractive -ExecutionPolicy Bypass -File ' . escapeshellarg($scriptPath);
    exec($command . ' 2>&1', $output, $exitCode);
    @unlink($scriptPath);

    $header = "PRINTER_DIAGNOSTIC|WO={$workOrder->code}|Printer={$printer}\n";
    return response($header . implode("\n", $output) . "\nEXIT_CODE={$exitCode}\n", $exitCode === 0 ? 200 : 500, ['Content-Type' => 'text/plain; charset=UTF-8']);
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