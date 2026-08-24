<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpnameItem extends Model
{
    protected $fillable = [
        'stock_opname_id',
        'product_id',
        'system_quantity',
        'physical_quantity',
        'difference_quantity',
        'unit_cost',
        'difference_value',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'system_quantity' => 'integer',
            'physical_quantity' => 'integer',
            'difference_quantity' => 'integer',
            'unit_cost' => 'decimal:2',
            'difference_value' => 'decimal:2',
        ];
    }

    public function stockOpname(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

