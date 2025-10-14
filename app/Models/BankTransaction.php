<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BankTransaction extends Model
{
    protected $fillable = [
        'bank_account_id',
        'transaction_type',
        'transactionable_type',
        'transactionable_id',
        'amount',
        'transaction_date',
        'description',
        'balance_after',
        'reference',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function transactionable(): MorphTo
    {
        return $this->morphTo();
    }

    // Scope for statement generation
    public function scopeForAccount($query, $accountId)
    {
        return $query->where('bank_account_id', $accountId);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }
}
