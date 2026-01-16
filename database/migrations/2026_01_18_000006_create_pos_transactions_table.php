<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Cashier/Staff
            $table->foreignId('customer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('transaction_number')->unique();
            $table->enum('transaction_type', ['sale', 'return', 'exchange'])->default('sale');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2);
            $table->decimal('change_amount', 10, 2)->default(0);
            $table->enum('payment_method', ['cash', 'card', 'mobile_payment', 'bank_transfer', 'credit'])->default('cash');
            $table->enum('status', ['completed', 'pending', 'cancelled', 'refunded'])->default('completed');
            $table->json('items')->nullable(); // Array of sold items
            $table->text('notes')->nullable();
            $table->string('receipt_file')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('customer_id');
            $table->index('transaction_number');
            $table->index('transaction_type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_transactions');
    }
};
