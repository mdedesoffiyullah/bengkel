<?php

namespace App\Http\Controllers;

use App\Models\StockOpname;
use App\Models\InventoryBalance;
use App\Models\InventoryLayer;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockOpnameItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use RuntimeException;

class StockOpnameController extends Controller
{
    public function index(Request $request)
    {
        $query = StockOpname::withCount('items');
        if ($request->filled('status')) $query->where('status', $request->status);
        $stockOpnames = $query->latest()->paginate(20)->withQueryString();
        return view('stock_opnames.index', compact('stockOpnames'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->with(['inventoryBalance', 'inventoryLayers'])->orderBy('name')->get();
        return view('stock_opnames.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:stock_opnames,code'],
            'opname_date' => 'required|date', 'notes' => 'nullable|string',
            'status' => ['nullable', Rule::in(['DRAFT', 'IN_PROGRESS'])],
        ]);
        $opname = StockOpname::create(['code' => $validated['code'], 'opname_date' => $validated['opname_date'], 'notes' => $validated['notes'] ?? null, 'status' => $validated['status'] ?? 'DRAFT']);
        return redirect()->route('stock-opnames.show', $opname)->with('success', 'Stock opname berhasil dibuat.');
    }

    public function show(StockOpname $stockOpname)
    {
        $stockOpname->load(['items.product']);
        $products = Product::where('is_active', true)->with(['inventoryBalance', 'inventoryLayers'])->orderBy('name')->get();
        return view('stock_opnames.show', compact('stockOpname', 'products'));
    }

    public function addItem(Request $request, StockOpname $stockOpname)
    {
        if (in_array($stockOpname->status, ['POSTED', 'CANCELLED'])) return back()->with('error', 'Stock opname yang sudah final tidak dapat diubah.');
        $validated = $request->validate([
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('is_active', true)],
            'physical_quantity' => 'required|numeric|min:0', 'notes' => 'nullable|string',
        ]);
        $balance = InventoryBalance::where('product_id', $validated['product_id'])->first();
        $layerQuery = InventoryLayer::where('product_id', $validated['product_id'])->where('remaining_quantity', '>', 0);
        $layerQuantity = (int) $layerQuery->sum('remaining_quantity');
        if ($balance && (int) $balance->quantity > 0) {
            $systemQuantity = (int) $balance->quantity; $unitCost = (float) $balance->average_cost;
        } else {
            $systemQuantity = $layerQuantity;
            $layerCostTotal = (float) (clone $layerQuery)->selectRaw('COALESCE(SUM(remaining_quantity * unit_cost), 0) as total_cost')->value('total_cost');
            $unitCost = $systemQuantity > 0 ? $layerCostTotal / $systemQuantity : 0;
        }
        $physicalQuantity = (int) $validated['physical_quantity'];
        $differenceQuantity = $physicalQuantity - $systemQuantity;
        StockOpnameItem::updateOrCreate(
            ['stock_opname_id' => $stockOpname->id, 'product_id' => $validated['product_id']],
            ['system_quantity' => $systemQuantity, 'physical_quantity' => $physicalQuantity, 'difference_quantity' => $differenceQuantity, 'unit_cost' => $unitCost, 'difference_value' => $differenceQuantity * $unitCost, 'notes' => $validated['notes'] ?? null]
        );
        return redirect()->route('stock-opnames.show', $stockOpname)->with('success', 'Item stock opname berhasil ditambahkan.');
    }

    /** Posting opname benar-benar menyesuaikan inventory balance + FIFO layer. */
    public function post(StockOpname $stockOpname)
    {
        if (!in_array($stockOpname->status, ['DRAFT', 'IN_PROGRESS'])) return back()->with('error', 'Stock opname sudah final atau dibatalkan.');
        $stockOpname->load('items');
        if ($stockOpname->items->isEmpty()) return back()->with('error', 'Stock opname belum memiliki item.');

        try {
            DB::transaction(function () use ($stockOpname) {
                foreach ($stockOpname->items as $item) {
                    $difference = (int) $item->difference_quantity;
                    if ($difference === 0) continue;

                    $balance = InventoryBalance::where('product_id', $item->product_id)->lockForUpdate()->first();
                    if (!$balance) {
                        $balance = InventoryBalance::create(['product_id' => $item->product_id, 'quantity' => 0, 'reserved_quantity' => 0, 'available_quantity' => 0, 'average_cost' => 0]);
                    }

                    $movementQty = abs($difference);
                    $totalCost = 0;
                    if ($difference > 0) {
                        $unitCost = (float) $item->unit_cost;
                        InventoryLayer::create([
                            'product_id' => $item->product_id, 'purchase_item_id' => null, 'stock_movement_id' => null,
                            'quantity' => $movementQty, 'remaining_quantity' => $movementQty, 'unit_cost' => $unitCost,
                            'total_cost' => $movementQty * $unitCost, 'received_at' => $stockOpname->opname_date, 'status' => 'ACTIVE',
                        ]);
                        $movement = StockMovement::create([
                            'product_id' => $item->product_id, 'type' => 'STOCK_OPNAME', 'quantity' => $movementQty,
                            'unit_cost' => $unitCost, 'reference_type' => 'stock_opname', 'reference_id' => $stockOpname->id,
                            'moved_at' => $stockOpname->opname_date, 'notes' => 'Adjustment positif Stock Opname ' . $stockOpname->code,
                        ]);
                        $layer = InventoryLayer::where('product_id', $item->product_id)->whereNull('stock_movement_id')->where('quantity', $movementQty)->latest('id')->firstOrFail();
                        $layer->update(['stock_movement_id' => $movement->id]);
                    } else {
                        if ($movementQty > (int) $balance->quantity) throw new RuntimeException('Stok fisik tidak mencukupi untuk adjustment Stock Opname: ' . $item->product->name);
                        $movement = StockMovement::create([
                            'product_id' => $item->product_id, 'type' => 'STOCK_OPNAME', 'quantity' => $movementQty,
                            'unit_cost' => 0, 'reference_type' => 'stock_opname', 'reference_id' => $stockOpname->id,
                            'moved_at' => $stockOpname->opname_date, 'notes' => 'Adjustment negatif Stock Opname ' . $stockOpname->code,
                        ]);
                        $remaining = $movementQty;
                        $layers = InventoryLayer::where('product_id', $item->product_id)->where('remaining_quantity', '>', 0)->orderBy('received_at')->orderBy('id')->lockForUpdate()->get();
                        foreach ($layers as $layer) {
                            if ($remaining <= 0) break;
                            $consume = min($remaining, (int) $layer->remaining_quantity);
                            $unitCost = (float) $layer->unit_cost;
                            $totalCost += $consume * $unitCost;
                            $newRemaining = (int) $layer->remaining_quantity - $consume;
                            $layer->update(['remaining_quantity' => $newRemaining, 'status' => $newRemaining > 0 ? 'ACTIVE' : 'DEPLETED']);
                            $remaining -= $consume;
                        }
                        if ($remaining > 0) throw new RuntimeException('Inventory layer tidak mencukupi untuk adjustment Stock Opname: ' . $item->product->name);
                        $movement->update(['unit_cost' => $movementQty > 0 ? $totalCost / $movementQty : 0]);
                    }

                    $layers = InventoryLayer::where('product_id', $item->product_id)->where('remaining_quantity', '>', 0)->get(['remaining_quantity', 'unit_cost']);
                    $quantity = (int) $layers->sum('remaining_quantity');
                    $cost = $layers->sum(fn ($layer) => (int) $layer->remaining_quantity * (float) $layer->unit_cost);
                    $reserved = (int) $balance->reserved_quantity;
                    $balance->update(['quantity' => $quantity, 'average_cost' => $quantity > 0 ? $cost / $quantity : 0, 'available_quantity' => max(0, $quantity - $reserved)]);
                }
                $stockOpname->update(['status' => 'POSTED', 'approved_at' => now()]);
            });
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
        return redirect()->route('stock-opnames.show', $stockOpname)->with('success', 'Stock Opname berhasil diposting dan inventory disesuaikan.');
    }

    public function edit(StockOpname $stockOpname)
    {
        if (in_array($stockOpname->status, ['POSTED', 'CANCELLED'])) return redirect()->route('stock-opnames.show', $stockOpname)->with('error', 'Stock opname yang sudah final tidak dapat diedit.');
        return view('stock_opnames.edit', compact('stockOpname'));
    }

    public function update(Request $request, StockOpname $stockOpname)
    {
        if (in_array($stockOpname->status, ['POSTED', 'CANCELLED'])) return back()->with('error', 'Stock opname yang sudah final tidak dapat diubah.');
        $validated = $request->validate(['opname_date' => 'required|date', 'notes' => 'nullable|string', 'status' => ['required', Rule::in(['DRAFT', 'IN_PROGRESS'])]]);
        $stockOpname->update($validated);
        return redirect()->route('stock-opnames.show', $stockOpname)->with('success', 'Stock opname berhasil diperbarui.');
    }

    public function destroy(StockOpname $stockOpname)
    {
        if ($stockOpname->status === 'POSTED') return back()->with('error', 'Stock opname yang sudah diposting tidak dapat dibatalkan.');
        $stockOpname->update(['status' => 'CANCELLED']);
        return redirect()->route('stock-opnames.index')->with('success', 'Stock opname berhasil dibatalkan.');
    }
}
