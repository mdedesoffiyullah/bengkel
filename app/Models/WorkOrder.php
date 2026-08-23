<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrder extends Model
{
    protected $fillable = [
        'code',
        'customer_id',
        'opened_at',
        'completed_at',
        'cancelled_at',
        'status',
        'type',
        'complaint',
        'diagnosis',
        'notes',
        'started_at',
        'subtotal',
        'discount',
        'grand_total',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'grand_total' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(WorkOrderItem::class);
    }

    public function additionalCharges(): HasMany
    {
        return $this->hasMany(WorkOrderAdditionalCharge::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}

