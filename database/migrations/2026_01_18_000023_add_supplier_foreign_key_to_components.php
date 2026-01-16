<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only add foreign key if suppliers table exists
        if (Schema::hasTable('suppliers') && Schema::hasColumn('components', 'supplier_id')) {
            try {
                Schema::table('components', function (Blueprint $table) {
                    $table->foreign('supplier_id')
                        ->references('id')
                        ->on('suppliers')
                        ->onDelete('set null');
                });
            } catch (\Exception $e) {
                // Foreign key might already exist, ignore
                if (strpos($e->getMessage(), 'Duplicate key name') === false && 
                    strpos($e->getMessage(), 'already exists') === false) {
                    throw $e;
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('components') && Schema::hasColumn('components', 'supplier_id')) {
            Schema::table('components', function (Blueprint $table) {
                $table->dropForeign(['supplier_id']);
            });
        }
    }
};
