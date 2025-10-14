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
        Schema::create('egg_dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('egg_production_id')->constrained('egg_daily_production')->cascadeOnDelete();
            $table->date('date');
            $table->integer('quantity');
            $table->enum('dispatch_type', ['owner_consumption', 'sale']);
            $table->string('recipient_name');
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('egg_dispatches');
    }
};
