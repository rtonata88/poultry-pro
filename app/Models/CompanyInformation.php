<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyInformation extends Model
{
    protected $table = 'company_information';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'tax_number',
        'vat_rate',
        'logo',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'bank_routing_number',
        'bank_swift_code',
        'bank_iban',
    ];

    protected $casts = [
        'vat_rate' => 'decimal:2',
    ];
}
