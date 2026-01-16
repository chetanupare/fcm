<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_type_id')->constrained('device_types')->onDelete('cascade');
            $table->foreignId('device_brand_id')->constrained('device_brands')->onDelete('cascade');
            $table->string('name'); // Model name
            $table->string('slug');
            $table->text('description')->nullable();
            $table->json('specifications')->nullable(); // Store additional specs as JSON
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['device_type_id', 'device_brand_id']);
            $table->index('slug');
            $table->index('is_active');
            
            // Unique constraint: same brand can't have duplicate model names for same device type
            $table->unique(['device_type_id', 'device_brand_id', 'name'], 'unique_device_model');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_models');
    }
};
