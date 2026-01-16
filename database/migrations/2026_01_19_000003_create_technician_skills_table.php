<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technician_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technician_id')->constrained('technicians')->onDelete('cascade');
            $table->foreignId('device_type_id')->nullable()->constrained('device_types')->onDelete('cascade');
            $table->enum('complexity_level', ['basic', 'intermediate', 'advanced', 'expert'])->default('basic');
            $table->string('specialization')->nullable(); // e.g., 'data_recovery', 'network_setup', 'warranty_repair'
            $table->json('certifications')->nullable(); // Array of certification names/IDs
            $table->integer('experience_years')->default(0);
            $table->integer('jobs_completed')->default(0); // Track jobs completed for this skill
            $table->decimal('success_rate', 5, 2)->nullable(); // Percentage of successful jobs
            $table->boolean('is_primary')->default(false); // Primary skill vs secondary
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('technician_id');
            $table->index('device_type_id');
            $table->index(['technician_id', 'device_type_id']);
            $table->index('complexity_level');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technician_skills');
    }
};
