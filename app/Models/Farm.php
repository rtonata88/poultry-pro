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
     * Available = Sum of all closing stocks - Sum of all dispatches
     */
    public function availableEggStock(): int
    {
        // Get all egg production records for this farm's coops/flocks
        $totalClosingStock = EggDailyProduction::whereHas('flock.coop', function ($query) {
            $query->where('farm_id', $this->id);
        })->sum('closing_stock');

        // Get total eggs already dispatched from this farm
        $totalDispatched = $this->eggDispatches()->sum('quantity');

        return max(0, $totalClosingStock - $totalDispatched);
    }

    /**
     * Get available egg stock in trays (30 eggs per tray)
     */
    public function availableEggTrays(): int
    {
        return (int) floor($this->availableEggStock() / 30);
    }
}
