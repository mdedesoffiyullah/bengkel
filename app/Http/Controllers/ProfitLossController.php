<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\WorkOrder;
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

        // P&L memakai revenue yang sudah direalisasikan dari WO FINAL/COMPLETED,
        // bukan pembayaran customer. Pembayaran dipakai hanya untuk cash flow.
        $completedWoQuery = WorkOrder::query()
            ->where('status', 'COMPLETED')
            ->whereBetween('completed_at', [$start, $end]);

        $serviceRevenue = (float) WorkOrderItem::query()
            ->where('item_type', 'SERVICE')
            ->whereHas('workOrder', fn ($q) => $q->where('status', 'COMPLETED')->whereBetween('completed_at', [$start, $end]))
            ->sum('subtotal');

        $productRevenue = (float) WorkOrderItem::query()
            ->where('item_type', 'PRODUCT')
            ->whereHas('workOrder', fn ($q) => $q->where('status', 'COMPLETED')->whereBetween('completed_at', [$start, $end]))
            ->sum('subtotal');

        // HPP produk harus berasal dari konsumsi FIFO aktual, bukan last_buy_price
        // yang tersimpan pada WorkOrderItem. Ini mencegah Laba/Rugi salah ketika
        // satu produk mempunyai beberapa layer dengan harga berbeda.
        $productCost = (float) StockMovement::query()
            ->where('type', 'USAGE')
            ->whereBetween('moved_at', [$start, $end])
            ->whereHasMorph('reference', [WorkOrder::class], fn ($q) => $q->where('status', 'COMPLETED')->whereBetween('completed_at', [$start, $end]))
            ->sum('quantity');

        // StockMovement.unit_cost adalah weighted FIFO cost untuk satu movement.
        // Gunakan konsumsi layer agar nilai HPP benar-benar identik dengan biaya FIFO.
        $productCost = (float) \DB::table('inventory_layer_consumptions as ilc')
            ->join('work_orders as wo', 'wo.id', '=', 'ilc.work_order_id')
            ->where('wo.status', 'COMPLETED')
            ->whereBetween('wo.completed_at', [$start, $end])
            ->whereBetween('ilc.created_at', [$start, $end])
            ->sum('ilc.total_cost');

        // Stock opname negatif adalah kehilangan persediaan. Adjustment positif
        // hanya menaikkan nilai inventory dan bukan pendapatan.
        $stockOpnameLoss = (float) StockMovement::query()
            ->where('type', 'STOCK_OPNAME')
            ->whereBetween('moved_at', [$start, $end])
            ->where('unit_cost', '>', 0)
            ->whereHasMorph('reference', [\App\Models\StockOpname::class])
            ->sum(\DB::raw('quantity * unit_cost'));

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

        $completedWorkOrders = (int) $completedWoQuery->count();
        $customerPaymentCount = Payment::where('transaction_type', 'CUSTOMER_PAYMENT')->whereBetween('paid_at', [$start, $end])->count();
        $purchasePaymentCount = Payment::where('transaction_type', 'PURCHASE_PAYMENT')->whereBetween('paid_at', [$start, $end])->count();
        $purchaseCount = Purchase::whereBetween('purchase_date', [$startDate, $endDate])->whereNotIn('status', ['CANCELLED', 'DRAFT'])->count();

        return view('profit-loss.index', compact(
            'startDate',
            'endDate',
            'serviceRevenue',
            'productRevenue',
            'productCost',
            'stockOpnameLoss',
            'operatingExpenses',
            'customerReceipts',
            'purchasePayments',
            'purchaseCommitments',
            'totalRevenue',
            'grossProfit',
            'netProfit',
            'netCashFlow',
            'breakEvenRevenue',
            'breakEvenGap',
            'breakEvenReached',
            'completedWorkOrders',
            'customerPaymentCount',
            'purchasePaymentCount',
            'purchaseCount'
        ));
    }
}
