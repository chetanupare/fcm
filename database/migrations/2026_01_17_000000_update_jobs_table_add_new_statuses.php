<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update the enum to include all new statuses
        // Note: MySQL doesn't support ALTER ENUM directly, so we need to modify the column
        DB::statement("ALTER TABLE service_jobs MODIFY COLUMN status ENUM(
            'offered',
            'accepted',
            'en_route',
            'component_pickup',
            'arrived',
            'diagnosing',
            'quoted',
            'signed_contract',
            'repairing',
            'waiting_parts',
            'quality_check',
            'waiting_payment',
            'completed',
            'released',
            'cancelled',
            'no_show',
            'cannot_repair'
        ) DEFAULT 'offered'");
    }

    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE service_jobs MODIFY COLUMN status ENUM(
            'offered',
            'accepted',
            'en_route',
            'arrived',
            'diagnosing',
            'waiting_parts',
            'repairing',
            'quality_check',
            'completed',
            'cancelled',
            'no_show',
            'cannot_repair'
        ) DEFAULT 'offered'");
    }
};
