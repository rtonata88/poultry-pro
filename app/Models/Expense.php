<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Expense extends Model
{
    protected $fillable = [
        'expense_number',
        'expense_category_id',
        'supplier_id',
        'payment_method_id',
        'bank_account_id',
        'date',
        'amount',
        'vat',
        'total',
        'reference',
        'notes',
        'status',
        'document_path',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'vat' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function bankTransaction(): MorphOne
    {
        return $this->morphOne(BankTransaction::class, 'transactionable');
    }
}
