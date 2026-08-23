<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'code',
        'name',
        'phone',
        'notes',
        'plate_number',
        'brand',
        'type',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Customer dapat memiliki banyak Work Order.
     */
    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    /**
     * Customer dapat memiliki banyak Complaint.
     */
    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }
}