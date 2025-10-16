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
        Schema::table('feed_daily_usage', function (Blueprint $table) {
            $table->decimal('opening_stock', 10, 2)->default(0)->after('date');
            $table->decimal('received', 10, 2)->default(0)->after('opening_stock');
            $table->decimal('closing_stock', 10, 2)->default(0)->after('quantity_used');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feed_daily_usage', function (Blueprint $table) {
            $table->dropColumn(['opening_stock', 'received', 'closing_stock']);
        });
    }
};
