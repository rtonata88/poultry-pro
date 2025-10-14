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
        Schema::create('feed_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('farm_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('current_stock', 10, 2)->default(0);
            $table->decimal('reorder_level', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['feed_type_id', 'farm_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feed_inventory');
    }
};
