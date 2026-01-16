<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Add latitude and longitude for GPS location
            if (!Schema::hasColumn('tickets', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('address');
            }
            
            if (!Schema::hasColumn('tickets', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'longitude')) {
                $table->dropColumn('longitude');
            }
            if (Schema::hasColumn('tickets', 'latitude')) {
                $table->dropColumn('latitude');
            }
        });
    }
};
