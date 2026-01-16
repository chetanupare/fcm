<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('components', function (Blueprint $table) {
            if (!Schema::hasColumn('components', 'reorder_level')) {
                $table->integer('reorder_level')->default(5)->after('min_stock_level');
            }
            if (!Schema::hasColumn('components', 'reorder_quantity')) {
                $table->integer('reorder_quantity')->default(10)->after('reorder_level');
            }
            if (!Schema::hasColumn('components', 'barcode')) {
                $table->string('barcode')->unique()->nullable()->after('sku');
            }
            // Don't add supplier_id foreign key here - suppliers table doesn't exist yet
            // It will be added in a later migration after suppliers table is created
            if (!Schema::hasColumn('components', 'supplier_id')) {
                $table->unsignedBigInteger('supplier_id')->nullable()->after('brand_id');
            }
            if (!Schema::hasColumn('components', 'last_reorder_date')) {
                $table->date('last_reorder_date')->nullable()->after('reorder_quantity');
            }
            if (!Schema::hasColumn('components', 'alert_sent')) {
                $table->boolean('alert_sent')->default(false)->after('last_reorder_date');
            }
        });
        
        // Add foreign key constraint after suppliers table is created
        // This will be done in a separate migration that runs after suppliers table exists
    }

    public function down(): void
    {
        Schema::table('components', function (Blueprint $table) {
            $table->dropColumn([
                'reorder_level',
                'reorder_quantity',
                'barcode',
                'supplier_id',
                'last_reorder_date',
                'alert_sent'
            ]);
        });
    }
};
