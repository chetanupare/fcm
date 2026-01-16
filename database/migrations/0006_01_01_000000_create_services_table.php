<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('device_type', [
                'laptop',
                'phone',
                'ac',
                'fridge',
                'tablet',
                'desktop',
                'other',
                'universal'
            ])->nullable();
            $table->decimal('price', 10, 2);
            $table->enum('category', ['repair', 'diagnosis', 'part', 'visit_fee']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
