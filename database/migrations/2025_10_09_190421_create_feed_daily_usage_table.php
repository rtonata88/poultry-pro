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
        Schema::create('feed_daily_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flock_id')->constrained()->cascadeOnDelete();
            $table->foreignId('feed_type_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('quantity_used', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['flock_id', 'feed_type_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feed_daily_usage');
    }
};
