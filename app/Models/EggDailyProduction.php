<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EggDailyProduction extends Model
{
    protected $table = 'egg_daily_production';

    protected $fillable = [
        'flock_id',
        'date',
        'opening_stock',
        'eggs_produced',
        'damaged',
        'closing_stock',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'opening_stock' => 'integer',
        'eggs_produced' => 'integer',
        'damaged' => 'integer',
        'closing_stock' => 'integer',
    ];

    public function flock(): BelongsTo
    {
        return $this->belongsTo(Flock::class);
    }

    /**
     * Get the actual available stock at the farm level
     * Note: Dispatches are now tracked at farm level, not production level
     */
    public function availableStock(): int
    {
        // Get the farm for this production record
        $farm = $this->flock->coop->farm;

        // Return farm-level available stock
        return $farm->availableEggStock();
    }

    /**
     * Calculate production rate for this specific day
     * Production Rate = (Eggs Produced / Active Birds) × 100
     */
    public function productionRate(): float
    {
        // Get the most recent bird record up to this production date
        $birdRecord = BirdDailyRecord::where('flock_id', $this->flock_id)
            ->where('date', '<=', $this->date)
            ->orderBy('date', 'desc')
            ->first();

        if (!$birdRecord) {
            return 0;
        }

        // Use closing stock as active birds (current active birds)
        $activeBirds = $birdRecord->closing_stock;

        if ($activeBirds <= 0) {
            return 0;
        }

        return ($this->eggs_produced / $activeBirds) * 100;
    }
}
