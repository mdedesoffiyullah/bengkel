<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderItem extends Model
{
    protected $fillable = [
        'work_order_id',
        'item_type',
        'service_id',
        'product_id',

        // Data barang baru
        'category_id',
        'supplier_id',
        'item_code',
        'item_name',
        'barcode',
        'brand',
        'unit',
        'stock_type',
        'quantity',
        'minimum_stock',

        // Harga
        'unit_price',
        'discount_amount',
        'unit_cost',
        'selling_price',

        // Total
        'subtotal',
        'total_cost',

        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'minimum_stock' => 'decimal:3',

            'unit_price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'selling_price' => 'decimal:2',

            'subtotal' => 'decimal:2',
            'total_cost' => 'decimal:2',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
