<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedDailyUsage extends Model
{
    protected $table = 'feed_daily_usage';

    protected $fillable = [
        'flock_id',
        'feed_type_id',
        'date',
        'opening_stock',
        'received',
        'quantity_used',
        'closing_stock',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'opening_stock' => 'decimal:2',
        'received' => 'decimal:2',
        'quantity_used' => 'decimal:2',
        'closing_stock' => 'decimal:2',
    ];

    public function flock(): BelongsTo
    {
        return $this->belongsTo(Flock::class);
    }

    public function feedType(): BelongsTo
    {
        return $this->belongsTo(FeedType::class);
    }
}
