<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'vendor_category_id',
        'contact_person',
        'tax_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function vendorCategory(): BelongsTo
    {
        return $this->belongsTo(VendorCategory::class);
    }

    public function invoices()
    {
        return $this->hasMany(SupplierInvoice::class);
    }

    public function payments()
    {
        return $this->hasMany(SupplierPayment::class);
    }
}
