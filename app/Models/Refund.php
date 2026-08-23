<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    protected $fillable = [
        'invoice_id',
        'refund_date',
        'amount',
        'method',
        'reason',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'refund_date' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Invoice yang direfund.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * User yang membuat refund.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}