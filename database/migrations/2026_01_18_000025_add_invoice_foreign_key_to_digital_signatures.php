<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add foreign key constraint after invoices table is created
        if (Schema::hasTable('invoices') && Schema::hasTable('digital_signatures') && Schema::hasColumn('digital_signatures', 'invoice_id')) {
            Schema::table('digital_signatures', function (Blueprint $table) {
                // Check if foreign key doesn't already exist
                $foreignKeys = Schema::getConnection()
                    ->getDoctrineSchemaManager()
                    ->listTableForeignKeys('digital_signatures');
                
                $hasForeignKey = false;
                foreach ($foreignKeys as $foreignKey) {
                    if ($foreignKey->getColumns() == ['invoice_id']) {
                        $hasForeignKey = true;
                        break;
                    }
                }
                
                if (!$hasForeignKey) {
                    $table->foreign('invoice_id')
                        ->references('id')
                        ->on('invoices')
                        ->onDelete('set null');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('digital_signatures') && Schema::hasColumn('digital_signatures', 'invoice_id')) {
            Schema::table('digital_signatures', function (Blueprint $table) {
                $table->dropForeign(['invoice_id']);
            });
        }
    }
};
