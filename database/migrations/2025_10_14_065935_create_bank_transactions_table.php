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
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained()->onDelete('restrict');
            $table->enum('transaction_type', ['expense', 'supplier_payment', 'customer_payment', 'transfer_out', 'transfer_in']);
            $table->string('transactionable_type')->nullable(); // Polymorphic type
            $table->unsignedBigInteger('transactionable_id')->nullable(); // Polymorphic id
            $table->decimal('amount', 15, 2);
            $table->date('transaction_date');
            $table->string('description');
            $table->decimal('balance_after', 15, 2);
            $table->string('reference')->nullable();
            $table->timestamps();

            $table->index(['bank_account_id', 'transaction_date']);
            $table->index(['transactionable_type', 'transactionable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
