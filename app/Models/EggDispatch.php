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
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'quantity' => 'integer',
    ];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }
}
