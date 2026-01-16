<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('components', function (Blueprint $table) {
            // Add foreign keys if they don't exist
            if (!Schema::hasColumn('components', 'category_id')) {
                $table->unsignedBigInteger('category_id')->after('description');
            }
            if (!Schema::hasColumn('components', 'brand_id')) {
                $table->unsignedBigInteger('brand_id')->nullable()->after('category_id');
            }
            
            // Add other missing columns
            $columns = [
                'sku' => 'string',
                'cost_price' => 'decimal:10,2',
                'selling_price' => 'decimal:10,2',
                'stock_quantity' => 'integer',
                'min_stock_level' => 'integer',
                'unit' => 'string',
                'compatible_devices' => 'json',
                'compatible_brands' => 'json',
                'compatible_models' => 'json',
                'part_number' => 'string',
                'oem_part_number' => 'string',
                'image' => 'string',
                'is_active' => 'boolean',
                'is_consumable' => 'boolean',
                'total_used' => 'integer',
            ];

            foreach ($columns as $column => $type) {
                if (!Schema::hasColumn('components', $column)) {
                    if ($type === 'string') {
                        $table->string($column)->nullable();
                    } elseif (strpos($type, 'decimal') !== false) {
                        $table->decimal($column, 10, 2)->default(0);
                    } elseif ($type === 'integer') {
                        $table->integer($column)->default(0);
                    } elseif ($type === 'boolean') {
                        $table->boolean($column)->default(false);
                    } elseif ($type === 'json') {
                        $table->json($column)->nullable();
                    }
                }
            }

            // Add foreign key constraints
            if (Schema::hasTable('component_categories')) {
                $table->foreign('category_id')->references('id')->on('component_categories')->onDelete('restrict');
            }
            if (Schema::hasTable('component_brands')) {
                $table->foreign('brand_id')->references('id')->on('component_brands')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('components', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['brand_id']);
        });
    }
};
