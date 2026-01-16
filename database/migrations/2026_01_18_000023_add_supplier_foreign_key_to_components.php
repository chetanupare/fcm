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
            Schema::table('components', function (Blueprint $table) {
                // Check if foreign key doesn't already exist
                $foreignKeys = Schema::getConnection()
                    ->getDoctrineSchemaManager()
                    ->listTableForeignKeys('components');
                
                $hasForeignKey = false;
                foreach ($foreignKeys as $foreignKey) {
                    if ($foreignKey->getColumns() == ['supplier_id']) {
                        $hasForeignKey = true;
                        break;
                    }
                }
                
                if (!$hasForeignKey) {
                    $table->foreign('supplier_id')
                        ->references('id')
                        ->on('suppliers')
                        ->onDelete('set null');
                }
            });
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
