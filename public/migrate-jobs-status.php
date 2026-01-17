<?php

// Migration script to update jobs status enum to include component_pickup
// Run this script via web browser: https://yourdomain.com/migrate-jobs-status.php
// This script connects directly to MySQL and automatically reads config from .env file

echo "<h1>Jobs Status Enum Migration</h1>";
echo "<pre>";

// Function to parse .env file
function parseEnvFile($filePath) {
    $env = [];
    if (!file_exists($filePath)) {
        return $env;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos($line, '#') === 0) {
            continue;
        }

        // Parse key=value pairs
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Remove quotes if present
            $value = trim($value, '\'"');

            $env[$key] = $value;
        }
    }
    return $env;
}

// Read database configuration from .env file
$envPath = __DIR__ . '/../.env';
$env = parseEnvFile($envPath);

$host = $env['DB_HOST'] ?? 'localhost';
$port = $env['DB_PORT'] ?? '3306';
$database = $env['DB_DATABASE'] ?? 'laravel';
$username = $env['DB_USERNAME'] ?? 'root';
$password = $env['DB_PASSWORD'] ?? '';

echo "Reading database configuration from .env file...\n";
echo "Host: $host:$port\n";
echo "Database: $database\n";
echo "Username: $username\n";
echo "Password: " . (empty($password) ? '(empty)' : '****') . "\n\n";

try {
    echo "Connecting to database...\n";

    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✅ Database connection successful!\n\n";

    // Check current status values
    echo "Checking current status enum...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM service_jobs WHERE Field = 'status'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo "Current status enum: " . $result['Type'] . "\n\n";
    }

    // Update the enum to include all valid status values
    echo "Updating status enum...\n";
    $sql = "ALTER TABLE service_jobs MODIFY COLUMN status ENUM(
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
    ) DEFAULT 'offered'";

    $pdo->exec($sql);

    echo "✅ Migration completed successfully!\n";
    echo "The 'component_pickup' status is now available.\n\n";

    // Verify the change
    echo "Verifying changes...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM service_jobs WHERE Field = 'status'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo "Updated status enum: " . $result['Type'] . "\n\n";
    }

    // Test if component_pickup works
    echo "Testing component_pickup status...\n";
    $stmt = $pdo->prepare("UPDATE service_jobs SET status = 'component_pickup' WHERE id = 2");
    $stmt->execute();

    echo "✅ Test update successful! component_pickup status works.\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n\n";
    echo "Troubleshooting:\n";
    echo "1. Check your .env file exists and has correct DB_* values\n";
    echo "2. Make sure the database user has ALTER TABLE permissions\n";
    echo "3. Verify the table name 'service_jobs' exists\n";
    echo "4. Check if your hosting blocks external database connections\n\n";
    echo "Current .env values:\n";
    echo "DB_HOST: $host\n";
    echo "DB_PORT: $port\n";
    echo "DB_DATABASE: $database\n";
    echo "DB_USERNAME: $username\n\n";
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";
echo "<hr>";
echo "<h2>Alternative Manual SQL Query:</h2>";
echo "<p>If the script fails, run this SQL query directly in your database:</p>";
echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ccc;'>";
echo "ALTER TABLE service_jobs MODIFY COLUMN status ENUM(
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
) DEFAULT 'offered';";
echo "</pre>";
echo "<p><strong>Important:</strong> Delete this file after successful migration for security reasons.</p>";