<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAllocation extends Model
{
    protected $fillable = [
        'product_id',
        'work_order_id',
        'quantity',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    /**
     * Work Order yang menggunakan alokasi stok.
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    /**
     * Produk yang dialokasikan.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

