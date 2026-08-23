<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    /**
     * Menampilkan daftar pengeluaran.
     */
    public function index(Request $request)
    {
        $query = Expense::query();

        if ($request->filled('expense_type')) {
            $query->where(
                'expense_type',
                'like',
                '%' . $request->expense_type . '%'
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }

        if ($request->filled('date_from')) {
            $query->whereDate(
                'expense_date',
                '>=',
                $request->date_from
            );
        }

        if ($request->filled('date_to')) {
            $query->whereDate(
                'expense_date',
                '<=',
                $request->date_to
            );
        }

        $totalExpense = (clone $query)->sum('amount');

        $expenses = $query
            ->latest('expense_date')
            ->paginate(20)
            ->withQueryString();

        return view(
            'expenses.index',
            compact(
                'expenses',
                'totalExpense'
            )
        );
    }

    /**
     * Form membuat expense.
     */
    public function create()
    {
        return view('expenses.create');
    }

    /**
     * Menyimpan expense.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_type' => [
                'required',
                'string',
                'max:100',
            ],

            'expense_date' => [
                'required',
                'date',
            ],

            'description' => [
                'required',
                'string',
                'max:255',
            ],

            'amount' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'payment_method' => [
                'required',
                Rule::in([
                    'CASH',
                    'BANK_TRANSFER',
                    'DEBIT_CARD',
                    'CREDIT_CARD',
                    'QRIS',
                    'OTHER',
                ]),
            ],

            'reference_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        $expense = DB::transaction(
            function () use ($validated) {
                return Expense::create([
                    'code' => 'EXP-' . now()->format('YmdHis'),

                    'expense_type' =>
                        $validated['expense_type'],

                    'expense_date' =>
                        $validated['expense_date'],

                    'description' =>
                        $validated['description'],

                    'amount' =>
                        $validated['amount'],

                    'payment_method' =>
                        $validated['payment_method'],

                    'reference_number' =>
                        $validated['reference_number']
                        ?? null,

                    'status' =>
                        'POSTED',

                    'notes' =>
                        $validated['notes']
                        ?? null,
                ]);
            }
        );

        return redirect()
            ->route(
                'expenses.show',
                $expense
            )
            ->with(
                'success',
                'Pengeluaran berhasil dicatat.'
            );
    }

    /**
     * Detail expense.
     */
    public function show(Expense $expense)
    {
        return view(
            'expenses.show',
            compact('expense')
        );
    }

    /**
     * Form edit expense.
     */
    public function edit(Expense $expense)
    {
        if ($expense->status === 'POSTED') {
            return redirect()
                ->route(
                    'expenses.show',
                    $expense
                )
                ->with(
                    'error',
                    'Expense yang sudah diposting tidak dapat diedit.'
                );
        }

        return view(
            'expenses.edit',
            compact('expense')
        );
    }

    /**
     * Update expense.
     */
    public function update(
        Request $request,
        Expense $expense
    ) {
        if ($expense->status === 'POSTED') {
            return back()
                ->with(
                    'error',
                    'Expense yang sudah diposting tidak dapat diubah.'
                );
        }

        $validated = $request->validate([
            'expense_type' => [
                'required',
                'string',
                'max:100',
            ],

            'expense_date' => [
                'required',
                'date',
            ],

            'description' => [
                'required',
                'string',
                'max:255',
            ],

            'amount' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'payment_method' => [
                'required',
                Rule::in([
                    'CASH',
                    'BANK_TRANSFER',
                    'DEBIT_CARD',
                    'CREDIT_CARD',
                    'QRIS',
                    'OTHER',
                ]),
            ],

            'reference_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        $expense->update($validated);

        return redirect()
            ->route(
                'expenses.show',
                $expense
            )
            ->with(
                'success',
                'Expense berhasil diperbarui.'
            );
    }

    /**
     * Membatalkan expense.
     */
    public function destroy(Expense $expense)
    {
        if ($expense->status === 'POSTED') {
            $expense->update([
                'status' => 'CANCELLED',
            ]);

            return redirect()
                ->route(
                    'expenses.index'
                )
                ->with(
                    'success',
                    'Expense berhasil dibatalkan.'
                );
        }

        $expense->delete();

        return redirect()
            ->route(
                'expenses.index'
            )
            ->with(
                'success',
                'Expense berhasil dihapus.'
            );
    }
}
