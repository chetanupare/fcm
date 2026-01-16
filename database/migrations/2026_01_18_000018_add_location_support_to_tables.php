<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add location_id to tickets
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'location_id')) {
                $table->foreignId('location_id')->nullable()->after('customer_id')->constrained('locations')->onDelete('set null');
                $table->index('location_id');
            }
        });

        // Add location_id to service_jobs
        Schema::table('service_jobs', function (Blueprint $table) {
            if (!Schema::hasColumn('service_jobs', 'location_id')) {
                $table->foreignId('location_id')->nullable()->after('ticket_id')->constrained('locations')->onDelete('set null');
                $table->index('location_id');
            }
        });

        // Add location_id to technicians
        Schema::table('technicians', function (Blueprint $table) {
            if (!Schema::hasColumn('technicians', 'location_id')) {
                $table->foreignId('location_id')->nullable()->after('user_id')->constrained('locations')->onDelete('set null');
                $table->index('location_id');
            }
        });

        // Add location_id to components (inventory per location)
        Schema::table('components', function (Blueprint $table) {
            if (!Schema::hasColumn('components', 'location_id')) {
                $table->foreignId('location_id')->nullable()->after('id')->constrained('locations')->onDelete('set null');
                $table->index('location_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropColumn('location_id');
        });

        Schema::table('service_jobs', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropColumn('location_id');
        });

        Schema::table('technicians', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropColumn('location_id');
        });

        Schema::table('components', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropColumn('location_id');
        });
    }
};
