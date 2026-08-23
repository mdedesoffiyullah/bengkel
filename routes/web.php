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


/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');


/*
|--------------------------------------------------------------------------
| Master Data
|--------------------------------------------------------------------------
*/

Route::resource('customers', CustomerController::class);

Route::resource('vehicles', VehicleController::class);

Route::resource(
    'product-categories',
    ProductCategoryController::class
);

Route::resource('products', ProductController::class);

Route::resource('suppliers', SupplierController::class);

Route::resource('services', ServiceController::class);


/*
|--------------------------------------------------------------------------
| Work Order
|--------------------------------------------------------------------------
*/

Route::resource(
    'work-orders',
    WorkOrderController::class
);

Route::resource(
    'work-order-items',
    WorkOrderItemController::class
);

Route::resource(
    'work-order-additional-charges',
    WorkOrderAdditionalChargeController::class
);


/*
|--------------------------------------------------------------------------
| Purchasing
|--------------------------------------------------------------------------
*/

Route::resource(
    'purchases',
    PurchaseController::class
);

Route::resource(
    'purchase-items',
    PurchaseItemController::class
);


/*
|--------------------------------------------------------------------------
| Inventory
|--------------------------------------------------------------------------
*/

Route::resource(
    'inventory-balances',
    InventoryBalanceController::class
);

Route::resource(
    'inventory-layers',
    InventoryLayerController::class
);

Route::resource(
    'stock-allocations',
    StockAllocationController::class
);

Route::resource(
    'stock-movements',
    StockMovementController::class
);

Route::resource(
    'stock-opnames',
    StockOpnameController::class
);

Route::resource(
    'stock-opname-items',
    StockOpnameItemController::class
);


/*
|--------------------------------------------------------------------------
| Finance
|--------------------------------------------------------------------------
*/

Route::resource(
    'payments',
    PaymentController::class
);

Route::get(
    'profit-loss',
    [ProfitLossController::class, 'index']
)->name('profit-loss.index');

Route::resource(
    'invoices',
    InvoiceController::class
);

Route::resource(
    'refunds',
    RefundController::class
);


Route::resource(
    'expenses',
    ExpenseController::class
);


/*
|--------------------------------------------------------------------------
| Customer Service
|--------------------------------------------------------------------------
*/

Route::resource(
    'complaints',
    ComplaintController::class
);


/*
|--------------------------------------------------------------------------
| Audit
|--------------------------------------------------------------------------
*/

Route::resource(
    'activity-logs',
    ActivityLogController::class
);
Route::patch('/work-orders/{workOrder}/final', [App\Http\Controllers\WorkOrderController::class, 'final'])
    ->name('work-orders.final');



