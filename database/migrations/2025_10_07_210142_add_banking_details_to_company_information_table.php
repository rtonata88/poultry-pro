<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('company_information', function (Blueprint $table) {
            $table->string('bank_name')->nullable()->after('vat_rate');
            $table->string('bank_account_name')->nullable()->after('bank_name');
            $table->string('bank_account_number')->nullable()->after('bank_account_name');
            $table->string('bank_routing_number')->nullable()->after('bank_account_number');
            $table->string('bank_swift_code')->nullable()->after('bank_routing_number');
            $table->string('bank_iban')->nullable()->after('bank_swift_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_information', function (Blueprint $table) {
            $table->dropColumn([
                'bank_name',
                'bank_account_name',
                'bank_account_number',
                'bank_routing_number',
                'bank_swift_code',
                'bank_iban',
            ]);
        });
    }
};
