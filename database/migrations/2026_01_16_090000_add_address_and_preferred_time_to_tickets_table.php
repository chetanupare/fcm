<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Add address field for service location
            if (!Schema::hasColumn('tickets', 'address')) {
                $table->text('address')->nullable()->after('issue_description');
            }
            
            // Add preferred date and time for service
            if (!Schema::hasColumn('tickets', 'preferred_date')) {
                $table->date('preferred_date')->nullable()->after('address');
            }
            
            if (!Schema::hasColumn('tickets', 'preferred_time')) {
                $table->time('preferred_time')->nullable()->after('preferred_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'preferred_time')) {
                $table->dropColumn('preferred_time');
            }
            if (Schema::hasColumn('tickets', 'preferred_date')) {
                $table->dropColumn('preferred_date');
            }
            if (Schema::hasColumn('tickets', 'address')) {
                $table->dropColumn('address');
            }
        });
    }
};
