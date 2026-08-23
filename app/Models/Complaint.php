<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    protected $fillable = [
        'code',
        'work_order_id',
        'customer_id',
        'vehicle_id',
        'complaint_date',
        'subject',
        'description',
        'status',
        'resolution',
        'resolved_at',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'complaint_date' => 'date',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Work Order yang terkait dengan complaint.
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    /**
     * Customer yang membuat complaint.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Kendaraan yang terkait complaint.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * User yang mencatat complaint.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}