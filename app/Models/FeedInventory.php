<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedInventory extends Model
{
    protected $table = 'feed_inventory';

    protected $fillable = [
        'feed_type_id',
        'farm_id',
        'current_stock',
        'reorder_level',
    ];

    protected $casts = [
        'current_stock' => 'decimal:2',
        'reorder_level' => 'decimal:2',
    ];

    public function feedType(): BelongsTo
    {
        return $this->belongsTo(FeedType::class);
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->reorder_level;
    }
}
