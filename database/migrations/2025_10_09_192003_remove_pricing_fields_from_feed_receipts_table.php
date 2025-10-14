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
        Schema::table('feed_receipts', function (Blueprint $table) {
            $table->dropColumn(['unit_price', 'total_cost']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feed_receipts', function (Blueprint $table) {
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('total_cost', 10, 2)->nullable();
        });
    }
};
