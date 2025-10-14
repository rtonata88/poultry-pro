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
        Schema::create('flocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coop_id')->constrained()->cascadeOnDelete();
            $table->string('batch_number')->unique();
            $table->string('breed')->nullable();
            $table->date('placement_date');
            $table->integer('initial_quantity');
            $table->string('source')->nullable();
            $table->enum('status', ['active', 'completed', 'transferred'])->default('active');
            $table->date('expected_end_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flocks');
    }
};
