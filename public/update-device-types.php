<?php
/**
 * Script to update devices with device_type_id based on device_type string
 * 
 * Usage: https://your-domain.com/update-device-types.php
 * 
 * This will:
 * 1. Find all devices missing device_type_id
 * 2. Match them to device_types by name
 * 3. Update the device_type_id
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "<h1>Update Device Types</h1>";
echo "<p>This script will update devices with device_type_id based on their device_type string.</p>";
echo "<hr>";

try {
    // Get all device types - index by both name and slug for flexible matching
    $deviceTypesByName = \App\Models\DeviceType::all()->keyBy(function($type) {
        return strtolower($type->name);
    });
    
    $deviceTypesBySlug = \App\Models\DeviceType::all()->keyBy(function($type) {
        return strtolower($type->slug);
    });
    
    // Combined lookup - check both name and slug
    $deviceTypes = collect($deviceTypesByName)->merge($deviceTypesBySlug);
    
    // Common device type mappings (handle variations)
    $deviceTypeMappings = [
        'phone' => 'phone',  // Maps to slug 'phone' (which is "Mobile Phone")
        'mobile' => 'phone',
        'smartphone' => 'phone',
        'mobile phone' => 'phone',
        'laptop' => 'laptop',
        'notebook' => 'laptop',
        'desktop' => 'desktop',
        'pc' => 'desktop',
        'tablet' => 'tablet',
        'ipad' => 'tablet',
        'ac' => 'ac',
        'air conditioner' => 'ac',
        'fridge' => 'fridge',
        'refrigerator' => 'fridge',
        'printer' => 'printer',
        'scanner' => 'scanner',
    ];
    
    echo "<h2>Available Device Types</h2>";
    echo "<ul>";
    foreach ($deviceTypesByName as $name => $type) {
        echo "<li><strong>{$name}</strong> (slug: {$type->slug}) → ID: {$type->id}</li>";
    }
    echo "</ul>";
    echo "<hr>";
    
    // Check for missing device types and create them
    echo "<h2>Checking for Missing Device Types</h2>";
    $missingTypes = [];
    $devicesToCheck = \App\Models\Device::whereNull('device_type_id')
        ->whereNotNull('device_type')
        ->distinct()
        ->pluck('device_type')
        ->map(function($type) {
            return strtolower(trim($type));
        })
        ->unique();
    
    foreach ($devicesToCheck as $deviceTypeString) {
        $normalizedType = $deviceTypeMappings[$deviceTypeString] ?? $deviceTypeString;
        
        // Check if exists by slug or name
        $exists = $deviceTypesBySlug->has($normalizedType) || $deviceTypesByName->has($normalizedType);
        
        if (!$exists) {
            $missingTypes[] = $normalizedType;
        }
    }
    
    if (!empty($missingTypes)) {
        echo "<p style='color: orange;'><strong>Missing device types found:</strong> " . implode(', ', array_unique($missingTypes)) . "</p>";
        echo "<p>Creating missing device types...</p>";
        
        foreach (array_unique($missingTypes) as $typeName) {
            $deviceType = \App\Models\DeviceType::firstOrCreate(
                ['name' => ucfirst($typeName)],
                [
                    'slug' => \Illuminate\Support\Str::slug($typeName),
                    'is_active' => true,
                    'sort_order' => 0,
                ]
            );
            
            $deviceTypes->put(strtolower($typeName), $deviceType);
            echo "<p style='color: green;'>✓ Created device type: <strong>{$deviceType->name}</strong> (ID: {$deviceType->id})</p>";
        }
        
        echo "<hr>";
    } else {
        echo "<p style='color: green;'>✓ All required device types exist!</p>";
        echo "<hr>";
    }
    
    // Find devices missing device_type_id
    $devicesToUpdate = \App\Models\Device::whereNull('device_type_id')
        ->whereNotNull('device_type')
        ->get();
    
    echo "<h2>Devices Needing Update</h2>";
    echo "<p><strong>Found:</strong> {$devicesToUpdate->count()} devices without device_type_id</p>";
    
    if ($devicesToUpdate->isEmpty()) {
        echo "<p style='color: green;'>✓ All devices already have device_type_id set!</p>";
    } else {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Device ID</th><th>Device Type (string)</th><th>Brand</th><th>Model</th><th>Status</th></tr>";
        
        $updated = 0;
        $failed = 0;
        
        foreach ($devicesToUpdate as $device) {
            $deviceTypeString = strtolower(trim($device->device_type ?? ''));
            $deviceType = null;
            
            // Try direct match by slug first (most reliable)
            if ($deviceTypesBySlug->has($deviceTypeString)) {
                $deviceType = $deviceTypesBySlug->get($deviceTypeString);
            }
            // Try direct match by name
            elseif ($deviceTypesByName->has($deviceTypeString)) {
                $deviceType = $deviceTypesByName->get($deviceTypeString);
            }
            // If no direct match, try mapping
            else {
                $normalizedType = $deviceTypeMappings[$deviceTypeString] ?? $deviceTypeString;
                // Try by slug first
                if ($deviceTypesBySlug->has($normalizedType)) {
                    $deviceType = $deviceTypesBySlug->get($normalizedType);
                }
                // Then try by name
                elseif ($deviceTypesByName->has($normalizedType)) {
                    $deviceType = $deviceTypesByName->get($normalizedType);
                }
            }
            
            if ($deviceType) {
                $device->device_type_id = $deviceType->id;
                $device->save();
                $updated++;
                $status = "<span style='color: green;'>✓ Updated (ID: {$deviceType->id}, Type: {$deviceType->name})</span>";
            } else {
                $failed++;
                $status = "<span style='color: red;'>✗ No match found for '{$device->device_type}'</span>";
            }
            
            echo "<tr>";
            echo "<td>{$device->id}</td>";
            echo "<td>{$device->device_type}</td>";
            echo "<td>{$device->brand}</td>";
            echo "<td>{$device->model}</td>";
            echo "<td>{$status}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<hr>";
        echo "<h2>Summary</h2>";
        echo "<ul>";
        echo "<li><strong>Updated:</strong> {$updated} devices</li>";
        echo "<li><strong>Failed:</strong> {$failed} devices (no matching device_type found)</li>";
        echo "</ul>";
        
        if ($failed > 0) {
            echo "<p style='color: orange;'><strong>Note:</strong> Some devices couldn't be matched. You may need to create missing device types or manually update them.</p>";
        }
    }
    
    echo "<hr>";
    echo "<h2>All Devices Status</h2>";
    
    $totalDevices = \App\Models\Device::count();
    $devicesWithTypeId = \App\Models\Device::whereNotNull('device_type_id')->count();
    $devicesWithoutTypeId = \App\Models\Device::whereNull('device_type_id')->count();
    
    echo "<ul>";
    echo "<li><strong>Total Devices:</strong> {$totalDevices}</li>";
    echo "<li><strong>With device_type_id:</strong> <span style='color: green;'>{$devicesWithTypeId}</span></li>";
    echo "<li><strong>Without device_type_id:</strong> <span style='color: red;'>{$devicesWithoutTypeId}</span></li>";
    echo "</ul>";
    
    // Show devices that still need manual update
    if ($devicesWithoutTypeId > 0) {
        $remainingDevices = \App\Models\Device::whereNull('device_type_id')
            ->whereNotNull('device_type')
            ->get();
        
        if ($remainingDevices->isNotEmpty()) {
            echo "<h3>Devices Still Needing Manual Update</h3>";
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>Device ID</th><th>Device Type (string)</th><th>Brand</th><th>Model</th></tr>";
            
            foreach ($remainingDevices as $device) {
                echo "<tr>";
                echo "<td>{$device->id}</td>";
                echo "<td>{$device->device_type}</td>";
                echo "<td>{$device->brand}</td>";
                echo "<td>{$device->model}</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            echo "<h3>SQL to Update Remaining Devices</h3>";
            echo "<p>If you want to manually update specific devices, use these SQL queries:</p>";
            echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
            
            foreach ($remainingDevices as $device) {
                $deviceTypeString = strtolower($device->device_type ?? '');
                $deviceType = $deviceTypes->get($deviceTypeString);
                
                if ($deviceType) {
                    echo "-- Update device #{$device->id} ({$device->device_type})\n";
                    echo "UPDATE devices SET device_type_id = {$deviceType->id} WHERE id = {$device->id};\n\n";
                } else {
                    echo "-- Device #{$device->id} ({$device->device_type}) - No matching device_type found\n";
                    echo "-- You need to create a device_type first or manually set the ID\n\n";
                }
            }
            echo "</pre>";
        }
    }
    
} catch (\Exception $e) {
    echo "<p style='color: red;'><strong>ERROR:</strong> " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
