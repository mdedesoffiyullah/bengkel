<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\StockOpnameItem;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfitLossController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $completedWo = fn ($q) => $q->where('status', 'COMPLETED')->whereBetween('completed_at', [$start, $end]);
        $serviceRevenue = (float) WorkOrderItem::where('item_type', 'SERVICE')->whereHas('workOrder', $completedWo)->sum('subtotal');
        $productRevenue = (float) WorkOrderItem::where('item_type', 'PRODUCT')->whereHas('workOrder', $completedWo)->sum('subtotal');

        // HPP berasal dari FIFO layer consumption aktual.
        $productCost = (float) DB::table('inventory_layer_consumptions as ilc')
            ->join('work_orders as wo', 'wo.id', '=', 'ilc.work_order_id')
            ->where('wo.status', 'COMPLETED')
            ->whereBetween('wo.completed_at', [$start, $end])
            ->sum('ilc.total_cost');

        // Opname minus adalah inventory loss. Opname plus hanya menambah aset inventory.
        $stockOpnameLoss = (float) StockOpnameItem::query()
            ->where('difference_quantity', '<', 0)
            ->whereHas('stockOpname', fn ($q) => $q->where('status', 'POSTED')->whereBetween('approved_at', [$start, $end]))
            ->sum(DB::raw('ABS(difference_value)'));

        $operatingExpenses = (float) Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount');
        $customerReceipts = (float) Payment::where('transaction_type', 'CUSTOMER_PAYMENT')->whereBetween('paid_at', [$start, $end])->sum('amount');
        $purchasePayments = (float) Payment::where('transaction_type', 'PURCHASE_PAYMENT')->whereBetween('paid_at', [$start, $end])->sum('amount');
        $purchaseCommitments = (float) Purchase::whereBetween('purchase_date', [$startDate, $endDate])->whereNotIn('status', ['CANCELLED', 'DRAFT'])->sum('grand_total');

        $totalRevenue = $serviceRevenue + $productRevenue;
        $grossProfit = $totalRevenue - $productCost;
        $netProfit = $grossProfit - $stockOpnameLoss - $operatingExpenses;
        $netCashFlow = $customerReceipts - $purchasePayments - $operatingExpenses;
        $breakEvenRevenue = $productCost + $stockOpnameLoss + $operatingExpenses;
        $breakEvenGap = max(0, $breakEvenRevenue - $totalRevenue);
        $breakEvenReached = $totalRevenue >= $breakEvenRevenue;

        $completedWorkOrders = WorkOrder::where('status', 'COMPLETED')->whereBetween('completed_at', [$start, $end])->count();
        $customerPaymentCount = Payment::where('transaction_type', 'CUSTOMER_PAYMENT')->whereBetween('paid_at', [$start, $end])->count();
        $purchasePaymentCount = Payment::where('transaction_type', 'PURCHASE_PAYMENT')->whereBetween('paid_at', [$start, $end])->count();
        $purchaseCount = Purchase::whereBetween('purchase_date', [$startDate, $endDate])->whereNotIn('status', ['CANCELLED', 'DRAFT'])->count();

        return view('profit-loss.index', compact(
            'startDate', 'endDate', 'serviceRevenue', 'productRevenue', 'productCost', 'stockOpnameLoss',
            'operatingExpenses', 'customerReceipts', 'purchasePayments', 'purchaseCommitments', 'totalRevenue',
            'grossProfit', 'netProfit', 'netCashFlow', 'breakEvenRevenue', 'breakEvenGap', 'breakEvenReached',
            'completedWorkOrders', 'customerPaymentCount', 'purchasePaymentCount', 'purchaseCount'
        ));
    }
}
