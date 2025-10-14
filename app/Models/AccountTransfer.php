<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class AccountTransfer extends Model
{
    protected $fillable = [
        'transfer_number',
        'from_account_id',
        'to_account_id',
        'amount',
        'transfer_date',
        'reference',
        'notes',
        'status',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'from_account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'to_account_id');
    }

    public function bankTransactions(): MorphMany
    {
        return $this->morphMany(BankTransaction::class, 'transactionable');
    }
}
