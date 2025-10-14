<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Flock extends Model
{
    protected $fillable = [
        'coop_id',
        'batch_number',
        'breed',
        'placement_date',
        'initial_quantity',
        'source',
        'status',
        'expected_end_date',
        'actual_end_date',
        'notes',
    ];

    protected $casts = [
        'placement_date' => 'date',
        'expected_end_date' => 'date',
        'actual_end_date' => 'date',
        'initial_quantity' => 'integer',
    ];

    public function coop(): BelongsTo
    {
        return $this->belongsTo(Coop::class);
    }

    public function birdDailyRecords(): HasMany
    {
        return $this->hasMany(BirdDailyRecord::class);
    }

    /**
     * Calculate age in weeks from placement date
     */
    public function ageInWeeks(?Carbon $date = null): int
    {
        $date = $date ?? now();
        return (int) floor($this->placement_date->diffInWeeks($date));
    }
}
