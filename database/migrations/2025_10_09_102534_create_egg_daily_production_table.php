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
        Schema::create('egg_daily_production', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flock_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->integer('opening_stock')->default(0);
            $table->integer('eggs_produced')->default(0);
            $table->integer('damaged')->default(0);
            $table->integer('closing_stock')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['flock_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('egg_daily_production');
    }
};
