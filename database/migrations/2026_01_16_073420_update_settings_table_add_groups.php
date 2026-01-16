<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For MySQL, we need to modify the enum
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE settings MODIFY COLUMN `group` ENUM('white_label', 'workflow', 'payment', 'localization', 'system', 'notifications') DEFAULT 'workflow'");
        } else {
            // For PostgreSQL and others
            Schema::table('settings', function (Blueprint $table) {
                $table->enum('group', ['white_label', 'workflow', 'payment', 'localization', 'system', 'notifications'])
                      ->default('workflow')
                      ->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE settings MODIFY COLUMN `group` ENUM('white_label', 'workflow', 'payment', 'localization') DEFAULT 'workflow'");
        } else {
            Schema::table('settings', function (Blueprint $table) {
                $table->enum('group', ['white_label', 'workflow', 'payment', 'localization'])
                      ->default('workflow')
                      ->change();
            });
        }
    }
};
