<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('po_number')->unique();
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();
            $table->enum('status', [
                'draft',
                'sent',
                'acknowledged',
                'partially_received',
                'received',
                'cancelled',
                'closed'
            ])->default('draft');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->json('line_items')->nullable(); // Array of items with quantities, prices
            $table->string('pdf_file')->nullable();
            $table->timestamps();
            
            $table->index('supplier_id');
            $table->index('created_by');
            $table->index('po_number');
            $table->index('status');
            $table->index('order_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
