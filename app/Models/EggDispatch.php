<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EggDispatch extends Model
{
    protected $fillable = [
        'farm_id',
        'date',
        'quantity',
        'dispatch_type',
        'dispatch_reason',
        'recipient_name',
        'sale_price',
        'total_amount',
        'payment_method_id',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'quantity' => 'integer',
        'sale_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
