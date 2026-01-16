<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technician_performance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technician_id')->constrained('technicians')->onDelete('cascade');
            $table->date('period_start'); // Monthly/Weekly period
            $table->date('period_end');
            $table->integer('total_jobs')->default(0);
            $table->integer('completed_jobs')->default(0);
            $table->integer('on_time_completions')->default(0);
            $table->integer('late_completions')->default(0);
            $table->decimal('average_rating', 3, 2)->nullable(); // 0.00 to 5.00
            $table->integer('total_ratings')->default(0);
            $table->integer('total_hours_worked')->default(0);
            $table->decimal('revenue_generated', 10, 2)->default(0);
            $table->integer('customer_satisfaction_score')->nullable(); // 0-100
            $table->integer('first_time_fix_rate')->nullable(); // Percentage
            $table->integer('rework_rate')->nullable(); // Percentage
            $table->json('metrics')->nullable(); // Additional custom metrics
            $table->timestamps();
            
            $table->unique(['technician_id', 'period_start', 'period_end']);
            $table->index('technician_id');
            $table->index('period_start');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technician_performance');
    }
};
