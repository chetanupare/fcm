<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('components')) {
            return;
        }
        
        Schema::create('components', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->decimal('cost_price', 10, 2)->default(0);
            $table->decimal('selling_price', 10, 2)->default(0);
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock_level')->default(5);
            $table->string('unit')->default('piece'); // piece, pack, box, etc.
            $table->json('compatible_devices')->nullable(); // device types this component works with
            $table->json('compatible_brands')->nullable(); // printer/scanner brands
            $table->json('compatible_models')->nullable(); // specific models
            $table->string('part_number')->nullable();
            $table->string('oem_part_number')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_consumable')->default(false); // toner, ink, etc.
            $table->integer('total_used')->default(0); // track usage for trends
            $table->timestamps();
            
            $table->index('category_id');
            $table->index('brand_id');
            $table->index('sku');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('components');
    }
};
