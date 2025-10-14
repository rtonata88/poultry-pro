<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('egg_dispatches', function (Blueprint $table) {
            // Add farm_id column (nullable initially for data migration)
            $table->foreignId('farm_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        // Populate farm_id from existing egg_production_id relationships
        DB::statement('
            UPDATE egg_dispatches
            SET farm_id = (
                SELECT c.farm_id
                FROM egg_daily_production edp
                JOIN flocks fl ON edp.flock_id = fl.id
                JOIN coops c ON fl.coop_id = c.id
                WHERE edp.id = egg_dispatches.egg_production_id
            )
        ');

        // Make farm_id NOT NULL after population
        Schema::table('egg_dispatches', function (Blueprint $table) {
            $table->foreignId('farm_id')->nullable(false)->change();
        });

        // Drop the old egg_production_id foreign key and column
        Schema::table('egg_dispatches', function (Blueprint $table) {
            $table->dropForeign(['egg_production_id']);
            $table->dropColumn('egg_production_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('egg_dispatches', function (Blueprint $table) {
            // Re-add egg_production_id
            $table->foreignId('egg_production_id')->after('id')->constrained('egg_daily_production')->cascadeOnDelete();
        });

        // Note: Cannot fully restore old data relationships

        Schema::table('egg_dispatches', function (Blueprint $table) {
            $table->dropForeign(['farm_id']);
            $table->dropColumn('farm_id');
        });
    }
};
