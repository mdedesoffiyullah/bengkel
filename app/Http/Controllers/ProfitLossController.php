<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\WorkOrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProfitLossController extends Controller
{
    /**
     * Laporan Laba & Rugi.
     */
    public function index(Request $request)
    {
        $startDate = $request->input(
            'start_date',
            now()->startOfMonth()->toDateString()
        );

        $endDate = $request->input(
            'end_date',
            now()->endOfMonth()->toDateString()
        );

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        /*
         * Pendapatan Jasa
         */
        $serviceRevenue = WorkOrderItem::query()
            ->where('item_type', 'SERVICE')
            ->whereHas('workOrder', function ($query) use ($start, $end) {
                $query
                    ->where('status', 'COMPLETED')
                    ->whereBetween('completed_at', [$start, $end]);
            })
            ->sum('subtotal');

        /*
         * Pendapatan Sparepart
         */
        $productRevenue = WorkOrderItem::query()
            ->where('item_type', 'PRODUCT')
            ->whereHas('workOrder', function ($query) use ($start, $end) {
                $query
                    ->where('status', 'COMPLETED')
                    ->whereBetween('completed_at', [$start, $end]);
            })
            ->sum('subtotal');

        /*
         * HPP Sparepart
         */
        $productCost = WorkOrderItem::query()
            ->where('item_type', 'PRODUCT')
            ->whereHas('workOrder', function ($query) use ($start, $end) {
                $query
                    ->where('status', 'COMPLETED')
                    ->whereBetween('completed_at', [$start, $end]);
            })
            ->sum('total_cost');

        /*
         * Expenses Operasional
         */
        $operatingExpenses = Expense::query()
            ->whereBetween('expense_date', [$start, $end])
            ->sum('amount');

        /*
         * Perhitungan
         */
        $totalRevenue = $serviceRevenue + $productRevenue;

        $grossProfit = $totalRevenue - $productCost;

        $netProfit = $grossProfit - $operatingExpenses;

        return view('profit-loss.index', compact(
            'startDate',
            'endDate',
            'serviceRevenue',
            'productRevenue',
            'productCost',
            'operatingExpenses',
            'totalRevenue',
            'grossProfit',
            'netProfit'
        ));
    }
}
