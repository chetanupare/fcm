<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Who created the expense
            $table->foreignId('job_id')->nullable()->constrained('service_jobs')->onDelete('set null');
            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->onDelete('set null');
            $table->string('expense_number')->unique();
            $table->enum('category', [
                'travel',
                'parts',
                'tools',
                'fuel',
                'parking',
                'meals',
                'accommodation',
                'communication',
                'supplies',
                'other'
            ])->default('other');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->date('expense_date');
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer', 'mobile_payment', 'other'])->default('cash');
            $table->string('receipt_file')->nullable();
            $table->json('attachments')->nullable(); // Multiple receipt images
            $table->enum('status', ['pending', 'approved', 'rejected', 'reimbursed'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('job_id');
            $table->index('category');
            $table->index('expense_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
