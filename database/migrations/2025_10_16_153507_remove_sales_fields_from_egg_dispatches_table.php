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
        Schema::table('egg_dispatches', function (Blueprint $table) {
            $table->dropForeign(['payment_method_id']);
            $table->dropColumn([
                'sale_price',
                'total_amount',
                'payment_method_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('egg_dispatches', function (Blueprint $table) {
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
        });
    }
};
