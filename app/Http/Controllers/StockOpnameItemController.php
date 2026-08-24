<?php

namespace App\Http\Controllers;

use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StockOpnameItemController extends Controller
{
    /**
     * Menampilkan daftar item stock opname.
     */
    public function index(Request $request)
    {
        $query = StockOpnameItem::with([
            'stockOpname',
            'product',
        ]);

        if ($request->filled('stock_opname_id')) {
            $query->where(
                'stock_opname_id',
                $request->stock_opname_id
            );
        }

        if ($request->filled('product_id')) {
            $query->where(
                'product_id',
                $request->product_id
            );
        }

        $items = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view(
            'stock_opname_items.index',
            compact('items')
        );
    }

    /**
     * Form menambahkan item ke stock opname.
     */
    public function create(Request $request)
    {
        $stockOpname = null;

        if ($request->filled('stock_opname_id')) {
            $stockOpname =
                StockOpname::findOrFail(
                    $request->stock_opname_id
                );
        }

        $opnames = StockOpname::whereIn(
            'status',
            [
                'DRAFT',
                'IN_PROGRESS',
            ]
        )
            ->latest()
            ->get();

        $products = Product::where(
            'is_active',
            true
        )
            ->orderBy('name')
            ->get();

        return view(
            'stock_opname_items.create',
            compact(
                'stockOpname',
                'opnames',
                'products'
            )
        );
    }

    /**
     * Menambahkan item ke stock opname.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'stock_opname_id' => [
                'required',
                'integer',
                Rule::exists(
                    'stock_opnames',
                    'id'
                ),
            ],

            'product_id' => [
                'required',
                'integer',
                Rule::exists(
                    'products',
                    'id'
                )->where(
                    'is_active',
                    true
                ),
            ],

            'physical_quantity' =>
                'required|numeric|min:0',

            'notes' =>
                'nullable|string',
        ]);

        $opname = StockOpname::findOrFail(
            $validated['stock_opname_id']
        );

        if (
            !in_array(
                $opname->status,
                [
                    'DRAFT',
                    'IN_PROGRESS',
                ]
            )
        ) {
            return back()
                ->with(
                    'error',
                    'Stock opname sudah final dan tidak dapat ditambahkan item.'
                )
                ->withInput();
        }

        /*
         * Ambil stok sistem saat opname dibuat.
         */
        $balance = InventoryBalance::where(
            'product_id',
            $validated['product_id']
        )->first();

        $systemQuantity =
            $balance
                ? (int) $balance->quantity
                : 0;

        $physicalQuantity =
            (float)
            $validated['physical_quantity'];

        $difference =
            $physicalQuantity
            - $systemQuantity;

        $item = StockOpnameItem::create([
            'stock_opname_id' =>
                $opname->id,

            'product_id' =>
                $validated['product_id'],

            'system_quantity' =>
                $systemQuantity,

            'physical_quantity' =>
                $physicalQuantity,

            'difference_quantity' =>
                $difference,

            'notes' =>
                $validated['notes'] ?? null,
        ]);

        /*
         * Setelah item pertama dibuat,
         * status opname bisa menjadi IN_PROGRESS.
         */
        if ($opname->status === 'DRAFT') {
            $opname->update([
                'status' => 'IN_PROGRESS',
            ]);
        }

        return redirect()
            ->route(
                'stock-opnames.show',
                $opname
            )
            ->with(
                'success',
                'Item stock opname berhasil ditambahkan.'
            );
    }

    /**
     * Detail item opname.
     */
    public function show(
        StockOpnameItem $stockOpnameItem
    ) {
        $stockOpnameItem->load([
            'stockOpname',
            'product',
        ]);

        return view(
            'stock_opname_items.show',
            compact('stockOpnameItem')
        );
    }

    /**
     * Form edit item opname.
     */
    public function edit(
        StockOpnameItem $stockOpnameItem
    ) {
        if (
            !in_array(
                $stockOpnameItem
                    ->stockOpname
                    ->status,
                [
                    'DRAFT',
                    'IN_PROGRESS',
                ]
            )
        ) {
            return redirect()
                ->route(
                    'stock-opname-items.show',
                    $stockOpnameItem
                )
                ->with(
                    'error',
                    'Item opname yang sudah final tidak dapat diedit.'
                );
        }

        return view(
            'stock_opname_items.edit',
            compact('stockOpnameItem')
        );
    }

    /**
     * Update hasil fisik opname.
     */
    public function update(
        Request $request,
        StockOpnameItem $stockOpnameItem
    ) {
        $stockOpnameItem->load(
            'stockOpname'
        );

        if (
            !in_array(
                $stockOpnameItem
                    ->stockOpname
                    ->status,
                [
                    'DRAFT',
                    'IN_PROGRESS',
                ]
            )
        ) {
            return back()
                ->with(
                    'error',
                    'Item opname yang sudah final tidak dapat diubah.'
                );
        }

        $validated = $request->validate([
            'physical_quantity' =>
                'required|numeric|min:0',

            'notes' =>
                'nullable|string',
        ]);

        $physicalQuantity =
            (float)
            $validated['physical_quantity'];

        $systemQuantity =
            (float)
            $stockOpnameItem->system_quantity;

        $stockOpnameItem->update([
            'physical_quantity' =>
                $physicalQuantity,

            'difference_quantity' =>
                $physicalQuantity
                - $systemQuantity,

            'notes' =>
                $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route(
                'stock-opnames.show',
                $stockOpnameItem->stock_opname_id
            )
            ->with(
                'success',
                'Hasil stock opname berhasil diperbarui.'
            );
    }

    /**
     * Item opname tidak langsung dihapus.
     */
    public function destroy(
        StockOpnameItem $stockOpnameItem
    ) {
        $stockOpnameItem->load(
            'stockOpname'
        );

        if (
            !in_array(
                $stockOpnameItem
                    ->stockOpname
                    ->status,
                [
                    'DRAFT',
                    'IN_PROGRESS',
                ]
            )
        ) {
            return back()
                ->with(
                    'error',
                    'Item opname yang sudah final tidak dapat dihapus.'
                );
        }

        $stockOpnameItem->delete();

        return redirect()
            ->route(
                'stock-opnames.show',
                $stockOpnameItem->stock_opname_id
            )
            ->with(
                'success',
                'Item stock opname berhasil dihapus.'
            );
    }
}
