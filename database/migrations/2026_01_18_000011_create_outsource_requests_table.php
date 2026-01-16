<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outsource_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('outsource_vendors')->onDelete('cascade');
            $table->foreignId('job_id')->nullable()->constrained('service_jobs')->onDelete('set null');
            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('request_number')->unique();
            $table->string('title');
            $table->text('description');
            $table->enum('status', [
                'pending',
                'sent',
                'accepted',
                'in_progress',
                'completed',
                'rejected',
                'cancelled'
            ])->default('pending');
            $table->decimal('quoted_amount', 10, 2)->nullable();
            $table->decimal('final_amount', 10, 2)->nullable();
            $table->date('requested_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->text('vendor_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();
            
            $table->index('vendor_id');
            $table->index('job_id');
            $table->index('status');
            $table->index('requested_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outsource_requests');
    }
};
