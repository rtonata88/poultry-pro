<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Farm extends Model
{
    protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'manager_name',
        'phone',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function coops(): HasMany
    {
        return $this->hasMany(Coop::class);
    }

    public function eggDispatches(): HasMany
    {
        return $this->hasMany(EggDispatch::class);
    }

    /**
     * Calculate total available egg stock for this farm
     * Available = Latest closing stock from the most recent production record
     */
    public function availableEggStock(): int
    {
        // Get the most recent egg production record for this farm's coops/flocks
        $latestProduction = EggDailyProduction::whereHas('flock.coop', function ($query) {
            $query->where('farm_id', $this->id);
        })
        ->orderBy('date', 'desc')
        ->first();

        // If no production records exist, return 0
        if (!$latestProduction) {
            return 0;
        }

        // The closing stock already accounts for everything:
        // opening stock + eggs produced - damaged - dispatched = closing stock
        return max(0, $latestProduction->closing_stock);
    }

    /**
     * Get available egg stock in trays (30 eggs per tray)
     */
    public function availableEggTrays(): int
    {
        return (int) floor($this->availableEggStock() / 30);
    }
}
