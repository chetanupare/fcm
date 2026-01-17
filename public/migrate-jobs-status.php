<?php

// Migration script to update jobs status enum to include component_pickup
// Run this script via web browser: https://yourdomain.com/migrate-jobs-status.php

require_once __DIR__.'/../bootstrap/app.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<h1>Jobs Status Enum Migration</h1>";
echo "<pre>";

try {
    echo "Starting migration...\n";

    // Check current status values
    $result = DB::select("SHOW COLUMNS FROM service_jobs WHERE Field = 'status'");
    if ($result) {
        echo "Current status enum: " . $result[0]->Type . "\n";
    }

    // Update the enum to include component_pickup
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

    echo "✅ Migration completed successfully!\n";
    echo "The 'component_pickup' status is now available.\n";

    // Verify the change
    $result = DB::select("SHOW COLUMNS FROM service_jobs WHERE Field = 'status'");
    if ($result) {
        echo "Updated status enum: " . $result[0]->Type . "\n";
    }

} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";
echo "<p><strong>Important:</strong> Delete this file after successful migration for security reasons.</p>";