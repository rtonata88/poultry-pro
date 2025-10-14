<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedReceipt extends Model
{
    protected $fillable = [
        'feed_type_id',
        'farm_id',
        'date',
        'quantity',
        'supplier',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'quantity' => 'decimal:2',
    ];

    public function feedType(): BelongsTo
    {
        return $this->belongsTo(FeedType::class);
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }
}
