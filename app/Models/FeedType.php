<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeedType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'unit',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function inventory(): HasMany
    {
        return $this->hasMany(FeedInventory::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(FeedReceipt::class);
    }

    public function dailyUsage(): HasMany
    {
        return $this->hasMany(FeedDailyUsage::class);
    }
}
