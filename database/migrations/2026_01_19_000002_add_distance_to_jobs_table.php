<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_jobs', function (Blueprint $table) {
            if (!Schema::hasColumn('service_jobs', 'distance_km')) {
                $table->decimal('distance_km', 8, 2)->nullable()->after('technician_id');
                $table->integer('estimated_duration_minutes')->nullable()->after('distance_km');
                $table->index('distance_km');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_jobs', function (Blueprint $table) {
            if (Schema::hasColumn('service_jobs', 'distance_km')) {
                $table->dropColumn(['distance_km', 'estimated_duration_minutes']);
            }
        });
    }
};
