<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    /**
     * Field yang boleh diisi melalui mass assignment.
     */
    protected $fillable = [
        'code',
        'name',
        'phone',
        'email',
        'contact_person',
        'address',
        'notes',
        'is_active',
    ];

    /**
     * Casting tipe data.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Satu supplier dapat memiliki banyak purchase.
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }
}