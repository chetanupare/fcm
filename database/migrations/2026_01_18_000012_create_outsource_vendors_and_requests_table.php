<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create outsource_vendors table
        Schema::create('outsource_vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone');
            $table->text('address')->nullable();
            $table->string('specialization')->nullable();
            $table->json('services_offered')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->decimal('rating', 3, 2)->nullable(); // 0.00 to 5.00
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('name');
        });

        // Create outsource_requests table
        Schema::create('outsource_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('outsource_vendors')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('job_id')->nullable()->constrained('service_jobs')->onDelete('set null');
            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->onDelete('set null');
            $table->string('request_number')->unique();
            $table->string('title');
            $table->text('description');
            $table->enum('status', ['pending', 'sent', 'accepted', 'in_progress', 'completed', 'rejected', 'cancelled'])->default('pending');
            $table->date('requested_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->decimal('quoted_amount', 10, 2)->nullable();
            $table->decimal('final_amount', 10, 2)->nullable();
            $table->text('vendor_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
            
            $table->index('vendor_id');
            $table->index('created_by');
            $table->index('job_id');
            $table->index('status');
            $table->index('request_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outsource_requests');
        Schema::dropIfExists('outsource_vendors');
    }
};
