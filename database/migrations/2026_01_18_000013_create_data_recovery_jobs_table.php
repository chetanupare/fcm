<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_recovery_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
            $table->foreignId('job_id')->nullable()->constrained('service_jobs')->onDelete('set null');
            $table->foreignId('technician_id')->nullable()->constrained('technicians')->onDelete('set null');
            $table->string('recovery_number')->unique();
            $table->enum('recovery_type', [
                'hard_drive',
                'ssd',
                'usb',
                'memory_card',
                'phone',
                'tablet',
                'cloud',
                'other'
            ])->default('hard_drive');
            $table->enum('failure_type', [
                'logical',
                'physical',
                'corruption',
                'deletion',
                'format',
                'virus',
                'other'
            ])->nullable();
            $table->enum('status', [
                'received',
                'assessment',
                'in_progress',
                'partial_recovery',
                'completed',
                'failed',
                'cancelled'
            ])->default('received');
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('final_cost', 10, 2)->nullable();
            $table->integer('estimated_data_size_gb')->nullable(); // Estimated data size
            $table->integer('recovered_data_size_gb')->nullable(); // Actual recovered data
            $table->decimal('recovery_percentage', 5, 2)->nullable(); // 0-100%
            $table->text('customer_requirements')->nullable(); // What files/data customer needs
            $table->text('recovery_notes')->nullable();
            $table->json('recovered_files_list')->nullable(); // List of recovered files/folders
            $table->string('delivery_method')->nullable(); // usb, external_drive, cloud, etc.
            $table->date('estimated_completion_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            $table->boolean('customer_notified')->default(false);
            $table->timestamps();
            
            $table->index('ticket_id');
            $table->index('job_id');
            $table->index('technician_id');
            $table->index('status');
            $table->index('recovery_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_recovery_jobs');
    }
};
