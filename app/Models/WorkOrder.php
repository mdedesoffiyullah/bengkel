<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

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

    protected static function booted(): void
    {
        static::updating(function (WorkOrder $workOrder) {
            if (!$workOrder->isDirty('grand_total')) {
                return;
            }

            $paid = (float) $workOrder->payments()
                ->where('transaction_type', 'CUSTOMER_PAYMENT')
                ->sum('amount');

            $newGrandTotal = (float) $workOrder->grand_total;

            if ($paid > $newGrandTotal) {
                throw ValidationException::withMessages([
                    'items' => 'Grand Total Work Order tidak boleh lebih kecil dari total pembayaran yang sudah tercatat. Pembayaran saat ini: Rp '
                        . number_format($paid, 0, ',', '.')
                        . '. Grand Total baru: Rp '
                        . number_format($newGrandTotal, 0, ',', '.')
                        . '. Koreksi/refund pembayaran terlebih dahulu.',
                ]);
            }
        });
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
