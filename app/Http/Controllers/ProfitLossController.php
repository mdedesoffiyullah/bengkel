<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\WorkOrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProfitLossController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $serviceRevenue = (float) WorkOrderItem::query()
            ->where('item_type', 'SERVICE')
            ->whereHas('workOrder', fn ($q) => $q->where('status','COMPLETED')->whereBetween('completed_at',[$start,$end]))
            ->sum('subtotal');

        $productRevenue = (float) WorkOrderItem::query()
            ->where('item_type', 'PRODUCT')
            ->whereHas('workOrder', fn ($q) => $q->where('status','COMPLETED')->whereBetween('completed_at',[$start,$end]))
            ->sum('subtotal');

        $productCost = (float) WorkOrderItem::query()
            ->where('item_type', 'PRODUCT')
            ->whereHas('workOrder', fn ($q) => $q->where('status','COMPLETED')->whereBetween('completed_at',[$start,$end]))
            ->sum('total_cost');

        $operatingExpenses = (float) Expense::whereBetween('expense_date',[$start,$end])->sum('amount');

        $customerReceipts = (float) Payment::where('transaction_type','CUSTOMER_PAYMENT')->whereBetween('paid_at',[$start,$end])->sum('amount');
        $purchasePayments = (float) Payment::where('transaction_type','PURCHASE_PAYMENT')->whereBetween('paid_at',[$start,$end])->sum('amount');
        $purchaseCommitments = (float) Purchase::whereBetween('purchase_date',[$startDate,$endDate])->whereNotIn('status',['CANCELLED','DRAFT'])->sum('grand_total');

        $totalRevenue = $serviceRevenue + $productRevenue;
        $grossProfit = $totalRevenue - $productCost;
        $netProfit = $grossProfit - $operatingExpenses;
        $netCashFlow = $customerReceipts - $purchasePayments - $operatingExpenses;
        $breakEvenRevenue = $productCost + $operatingExpenses;
        $breakEvenGap = max(0, $breakEvenRevenue - $totalRevenue);
        $breakEvenReached = $totalRevenue >= $breakEvenRevenue;

        $completedWorkOrders = \App\Models\WorkOrder::where('status','COMPLETED')->whereBetween('completed_at',[$start,$end])->count();
        $customerPaymentCount = Payment::where('transaction_type','CUSTOMER_PAYMENT')->whereBetween('paid_at',[$start,$end])->count();
        $purchasePaymentCount = Payment::where('transaction_type','PURCHASE_PAYMENT')->whereBetween('paid_at',[$start,$end])->count();
        $purchaseCount = Purchase::whereBetween('purchase_date',[$startDate,$endDate])->whereNotIn('status',['CANCELLED','DRAFT'])->count();

        return view('profit-loss.index', compact(
            'startDate','endDate','serviceRevenue','productRevenue','productCost','operatingExpenses',
            'customerReceipts','purchasePayments','purchaseCommitments','totalRevenue','grossProfit','netProfit',
            'netCashFlow','breakEvenRevenue','breakEvenGap','breakEvenReached','completedWorkOrders',
            'customerPaymentCount','purchasePaymentCount','purchaseCount'
        ));
    }
}
