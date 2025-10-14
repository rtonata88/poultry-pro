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
        Schema::table('bird_daily_records', function (Blueprint $table) {
            // Add index on date for date range queries
            $table->index('date');

            // Add index on mortality for filtering records with/without mortality
            $table->index('mortality');

            // Note: flock_id already has an index from the foreign key constraint
            // and there's already a unique composite index on (flock_id, date)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bird_daily_records', function (Blueprint $table) {
            $table->dropIndex(['date']);
            $table->dropIndex(['mortality']);
        });
    }
};
