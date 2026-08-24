<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryBalance extends Model
{
    protected $fillable = [
        'product_id',
        'quantity',
        'reserved_quantity',
        'available_quantity',
        'average_cost',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'reserved_quantity' => 'integer',
            'available_quantity' => 'integer',
            'average_cost' => 'decimal:2',
        ];
    }

    /**
     * Produk yang memiliki saldo inventory.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

