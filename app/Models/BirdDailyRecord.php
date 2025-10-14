<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BirdDailyRecord extends Model
{
    protected $fillable = [
        'flock_id',
        'date',
        'age_in_weeks',
        'opening_stock',
        'mortality',
        'culled',
        'sold',
        'closing_stock',
        'mortality_reason',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'age_in_weeks' => 'integer',
        'opening_stock' => 'integer',
        'mortality' => 'integer',
        'culled' => 'integer',
        'sold' => 'integer',
        'closing_stock' => 'integer',
    ];

    public function flock(): BelongsTo
    {
        return $this->belongsTo(Flock::class);
    }
}
