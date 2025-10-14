<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerQuotation extends Model
{
    protected $fillable = [
        'quotation_number',
        'customer_id',
        'date',
        'valid_until',
        'subtotal',
        'vat',
        'discount',
        'total',
        'status',
        'reference',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'valid_until' => 'date',
        'subtotal' => 'decimal:2',
        'vat' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CustomerQuotationItem::class);
    }
}
