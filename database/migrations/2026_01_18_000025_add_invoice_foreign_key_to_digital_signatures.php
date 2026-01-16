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
            try {
                Schema::table('digital_signatures', function (Blueprint $table) {
                    $table->foreign('invoice_id')
                        ->references('id')
                        ->on('invoices')
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
        if (Schema::hasTable('digital_signatures') && Schema::hasColumn('digital_signatures', 'invoice_id')) {
            Schema::table('digital_signatures', function (Blueprint $table) {
                $table->dropForeign(['invoice_id']);
            });
        }
    }
};
