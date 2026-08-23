<?php

namespace App\Http\Controllers;

use App\Models\StockOpname;
use App\Models\InventoryBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StockOpnameController extends Controller
{
    /**
     * Menampilkan daftar stock opname.
     */
    public function index(Request $request)
    {
        $query = StockOpname::query();

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }

        $opnames = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view(
            'stock_opnames.index',
            compact('opnames')
        );
    }

    /**
     * Form membuat stock opname.
     */
    public function create()
    {
        return view(
            'stock_opnames.create'
        );
    }

    /**
     * Membuat stock opname baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                'unique:stock_opnames,code',
            ],

            'opname_date' =>
                'required|date',

            'notes' =>
                'nullable|string',

            'status' => [
                'nullable',
                Rule::in([
                    'DRAFT',
                    'IN_PROGRESS',
                ]),
            ],
        ]);

        $opname = StockOpname::create([
            'code' =>
                $validated['code'],

            'opname_date' =>
                $validated['opname_date'],

            'notes' =>
                $validated['notes'] ?? null,

            'status' =>
                $validated['status']
                ?? 'DRAFT',
        ]);

        return redirect()
            ->route(
                'stock-opnames.show',
                $opname
            )
            ->with(
                'success',
                'Stock opname berhasil dibuat.'
            );
    }

    /**
     * Menampilkan detail stock opname.
     */
    public function show(
        StockOpname $stockOpname
    ) {
        $stockOpname->load([
            'items',
        ]);

        return view(
            'stock_opnames.show',
            compact('stockOpname')
        );
    }

    /**
     * Edit stock opname.
     */
    public function edit(
        StockOpname $stockOpname
    ) {
        if (
            in_array(
                $stockOpname->status,
                [
                    'POSTED',
                    'CANCELLED',
                ]
            )
        ) {
            return redirect()
                ->route(
                    'stock-opnames.show',
                    $stockOpname
                )
                ->with(
                    'error',
                    'Stock opname yang sudah final tidak dapat diedit.'
                );
        }

        return view(
            'stock_opnames.edit',
            compact('stockOpname')
        );
    }

    /**
     * Update header stock opname.
     */
    public function update(
        Request $request,
        StockOpname $stockOpname
    ) {
        if (
            in_array(
                $stockOpname->status,
                [
                    'POSTED',
                    'CANCELLED',
                ]
            )
        ) {
            return back()
                ->with(
                    'error',
                    'Stock opname yang sudah final tidak dapat diubah.'
                );
        }

        $validated = $request->validate([
            'opname_date' =>
                'required|date',

            'notes' =>
                'nullable|string',

            'status' => [
                'required',
                Rule::in([
                    'DRAFT',
                    'IN_PROGRESS',
                ]),
            ],
        ]);

        $stockOpname->update(
            $validated
        );

        return redirect()
            ->route(
                'stock-opnames.show',
                $stockOpname
            )
            ->with(
                'success',
                'Stock opname berhasil diperbarui.'
            );
    }

    /**
     * Membatalkan stock opname.
     *
     * Tidak menghapus data agar audit trail tetap ada.
     */
    public function destroy(
        StockOpname $stockOpname
    ) {
        if (
            $stockOpname->status === 'POSTED'
        ) {
            return back()
                ->with(
                    'error',
                    'Stock opname yang sudah diposting tidak dapat dibatalkan.'
                );
        }

        $stockOpname->update([
            'status' => 'CANCELLED',
        ]);

        return redirect()
            ->route(
                'stock-opnames.index'
            )
            ->with(
                'success',
                'Stock opname berhasil dibatalkan.'
            );
    }
}