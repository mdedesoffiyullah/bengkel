<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLayer extends Model
{
    protected $fillable = [
        'product_id',
        'purchase_item_id',
        'stock_movement_id',
        'quantity',
        'remaining_quantity',
        'unit_cost',
        'total_cost',
        'received_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'remaining_quantity' => 'decimal:3',
            'unit_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'received_at' => 'datetime',
        ];
    }

    /**
     * Produk yang dimiliki layer ini.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Purchase item yang menghasilkan layer.
     */
    public function purchaseItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseItem::class);
    }

    /**
     * Stock movement yang membentuk layer.
     */
    public function stockMovement(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class);
    }
}