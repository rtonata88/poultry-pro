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
        Schema::create('bird_daily_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flock_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->integer('age_in_weeks');
            $table->integer('opening_stock');
            $table->integer('mortality')->default(0);
            $table->integer('culled')->default(0);
            $table->integer('sold')->default(0);
            $table->integer('closing_stock');
            $table->string('mortality_reason')->nullable();
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
        Schema::dropIfExists('bird_daily_records');
    }
};
