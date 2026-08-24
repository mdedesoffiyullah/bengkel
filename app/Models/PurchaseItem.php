<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'work_order_item_id',
        'product_id',
        'quantity',
        'unit_cost',
        'selling_price',
        'discount_amount',
        'subtotal',
        'received_quantity',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_cost' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'received_quantity' => 'integer',
        ];
    }

    /**
     * Purchase induk.
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Produk yang dibeli.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Work Order Item yang menjadi sumber kebutuhan.
     */
    public function workOrderItem(): BelongsTo
    {
        return $this->belongsTo(
            WorkOrderItem::class,
            'work_order_item_id'
        );
    }
}

