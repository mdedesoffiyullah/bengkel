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
    $testPayload = "PRINTER TEST\r\n";

    $powershell = <<<'PS'
$ErrorActionPreference = 'Stop'
$printer = '__PRINTER__'
$raw = [System.Text.Encoding]::ASCII.GetBytes('__PAYLOAD__')

function Fail-Step($step, $message) {
    $code = [Runtime.InteropServices.Marshal]::GetLastWin32Error()
    throw "STEP_FAIL|$step|Win32Error=$code|$message"
}

try {
    $p = Get-Printer -Name $printer -ErrorAction Stop
    Write-Output ("PRINTER_FOUND|Name={0}|Status={1}|Driver={2}|Port={3}|WorkOffline={4}" -f $p.Name, $p.PrinterStatus, $p.DriverName, $p.PortName, $p.WorkOffline)

    Add-Type -TypeDefinition @'
using System;
using System.ComponentModel;
using System.Runtime.InteropServices;
public static class BengkelPrinterDiagnostic {
    [StructLayout(LayoutKind.Sequential, CharSet=CharSet.Unicode)]
    private class DOCINFO {
        [MarshalAs(UnmanagedType.LPWStr)] public string pDocName;
        [MarshalAs(UnmanagedType.LPWStr)] public string pOutputFile;
        [MarshalAs(UnmanagedType.LPWStr)] public string pDataType;
    }
    [DllImport("winspool.drv", SetLastError=true, CharSet=CharSet.Unicode)]
    private static extern bool OpenPrinter(string name, out IntPtr handle, IntPtr defaults);
    [DllImport("winspool.drv", SetLastError=true)] private static extern bool ClosePrinter(IntPtr handle);
    [DllImport("winspool.drv", SetLastError=true, CharSet=CharSet.Unicode)]
    private static extern int StartDocPrinter(IntPtr handle, int level, [In] DOCINFO doc);
    [DllImport("winspool.drv", SetLastError=true)] private static extern bool EndDocPrinter(IntPtr handle);
    [DllImport("winspool.drv", SetLastError=true)] private static extern bool StartPagePrinter(IntPtr handle);
    [DllImport("winspool.drv", SetLastError=true)] private static extern bool EndPagePrinter(IntPtr handle);
    [DllImport("winspool.drv", SetLastError=true)] private static extern bool WritePrinter(IntPtr handle, byte[] data, int count, out int written);

    public static string Run(string printer, byte[] data) {
        IntPtr h;
        if (!OpenPrinter(printer, out h, IntPtr.Zero)) {
            int e = Marshal.GetLastWin32Error();
            throw new Win32Exception(e, "OpenPrinter gagal");
        }
        Console.WriteLine("OPEN_PRINTER_OK");
        try {
            var doc = new DOCINFO { pDocName="Bengkel Printer Diagnostic", pOutputFile=null, pDataType="RAW" };
            int job = StartDocPrinter(h, 1, doc);
            if (job == 0) {
                int e = Marshal.GetLastWin32Error();
                throw new Win32Exception(e, "StartDocPrinter gagal");
            }
            Console.WriteLine("START_DOC_OK|JobId=" + job);
            try {
                if (!StartPagePrinter(h)) {
                    int e = Marshal.GetLastWin32Error();
                    throw new Win32Exception(e, "StartPagePrinter gagal");
                }
                Console.WriteLine("START_PAGE_OK");
                try {
                    int written;
                    bool ok = WritePrinter(h, data, data.Length, out written);
                    int error = Marshal.GetLastWin32Error();
                    Console.WriteLine("WRITE_PRINTER|Result=" + ok + "|Bytes=" + written + "|Expected=" + data.Length + "|LastError=" + error);
                    if (!ok) throw new Win32Exception(error, "WritePrinter gagal");
                    return "WRITE_OK";
                } finally {
                    bool endPage = EndPagePrinter(h);
                    int error = Marshal.GetLastWin32Error();
                    Console.WriteLine("END_PAGE|Result=" + endPage + "|LastError=" + error);
                }
            } finally {
                bool endDoc = EndDocPrinter(h);
                int error = Marshal.GetLastWin32Error();
                Console.WriteLine("END_DOC|Result=" + endDoc + "|LastError=" + error);
            }
        } finally {
            bool closed = ClosePrinter(h);
            int error = Marshal.GetLastWin32Error();
            Console.WriteLine("CLOSE_PRINTER|Result=" + closed + "|LastError=" + error);
        }
    }
}
'@

    [BengkelPrinterDiagnostic]::Run($printer, $raw)
    Write-Output 'DIAGNOSTIC_OK'
} catch {
    Write-Output ("DIAGNOSTIC_FAIL|Type={0}|Message={1}" -f $_.Exception.GetType().FullName, $_.Exception.Message)
    exit 1
}
PS;

    $powershell = str_replace(['__PRINTER__', '__PAYLOAD__'], [$printer, str_replace("'", "''", $testPayload)], $powershell);
    $encodedCommand = base64_encode(mb_convert_encoding($powershell, 'UTF-16LE', 'UTF-8'));
    $command = 'powershell.exe -NoProfile -NonInteractive -ExecutionPolicy Bypass -EncodedCommand ' . $encodedCommand;

    $output = [];
    $exitCode = 0;
    exec($command . ' 2>&1', $output, $exitCode);

    $header = "PRINTER_DIAGNOSTIC|WO={$workOrder->code}|Printer={$printer}\n";
    return response(
        $header . implode("\n", $output) . "\nEXIT_CODE={$exitCode}\n",
        $exitCode === 0 ? 200 : 500,
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
