<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technician_location_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technician_id')->constrained('technicians')->onDelete('cascade');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->timestamp('recorded_at');
            $table->string('source')->default('manual'); // manual, gps, api
            $table->json('metadata')->nullable(); // Additional data like accuracy, speed, etc.
            $table->timestamps();
            
            $table->index('technician_id');
            $table->index('recorded_at');
            $table->index(['technician_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technician_location_history');
    }
};
