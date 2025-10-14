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
        'quantity_used',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'quantity_used' => 'decimal:2',
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
