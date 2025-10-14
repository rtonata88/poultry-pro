<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerInvoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'customer_id',
        'customer_quotation_id',
        'date',
        'due_date',
        'subtotal',
        'vat',
        'discount',
        'total',
        'amount_paid',
        'balance',
        'status',
        'reference',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'vat' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(CustomerQuotation::class, 'customer_quotation_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CustomerInvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(CustomerPayment::class);
    }
}
