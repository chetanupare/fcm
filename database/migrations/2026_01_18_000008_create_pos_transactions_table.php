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
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('transaction_number')->unique();
            $table->enum('transaction_type', ['sale', 'return', 'exchange'])->default('sale');
            $table->json('items'); // Array of items with component_id, quantity, price
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->enum('payment_method', ['cash', 'card', 'mobile_payment', 'bank_transfer', 'credit'])->default('cash');
            $table->decimal('paid_amount', 10, 2);
            $table->decimal('change_amount', 10, 2)->default(0);
            $table->enum('status', ['pending', 'completed', 'cancelled', 'refunded'])->default('completed');
            $table->string('currency', 3)->default('USD');
            $table->text('notes')->nullable();
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
