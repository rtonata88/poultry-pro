<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coop extends Model
{
    protected $fillable = [
        'farm_id',
        'name',
        'code',
        'capacity',
        'type',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity' => 'integer',
    ];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function flocks(): HasMany
    {
        return $this->hasMany(Flock::class);
    }
}
