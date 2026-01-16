<?php
/**
 * Diagnostic script to test technician recommendations API
 * 
 * Usage: https://your-domain.com/test-recommendations.php?ticket_id=6
 * 
 * This script tests the recommendation endpoint directly without authentication
 * to help diagnose why recommendations aren't showing.
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$ticketId = $_GET['ticket_id'] ?? 6;

echo "<h1>Technician Recommendations Diagnostic</h1>";
echo "<p>Testing recommendations for Ticket ID: <strong>{$ticketId}</strong></p>";
echo "<hr>";

try {
    $ticket = \App\Models\Ticket::with('device.deviceType')->find($ticketId);
    
    if (!$ticket) {
        echo "<p style='color: red;'><strong>ERROR:</strong> Ticket #{$ticketId} not found!</p>";
        exit;
    }
    
    echo "<h2>Ticket Information</h2>";
    echo "<ul>";
    echo "<li><strong>Ticket ID:</strong> {$ticket->id}</li>";
    echo "<li><strong>Status:</strong> {$ticket->status}</li>";
    echo "<li><strong>Device ID:</strong> " . ($ticket->device_id ?? 'N/A') . "</li>";
    
    if ($ticket->device) {
        echo "<li><strong>Device Type (string):</strong> " . ($ticket->device->device_type ?? 'N/A') . "</li>";
        echo "<li><strong>Device Type ID:</strong> " . ($ticket->device->device_type_id ?? 'NULL') . "</li>";
        if ($ticket->device->deviceType) {
            echo "<li><strong>Device Type Name:</strong> " . $ticket->device->deviceType->name . "</li>";
        } else {
            echo "<li><strong>Device Type Name:</strong> <span style='color: red;'>NULL (Relationship not loaded)</span></li>";
        }
    } else {
        echo "<li><strong>Device:</strong> <span style='color: red;'>NULL</span></li>";
    }
    echo "</ul>";
    
    echo "<hr>";
    echo "<h2>Available Technicians</h2>";
    
    $technicians = \App\Models\Technician::where('status', 'on_duty')
        ->where(function($query) {
            $query->where('active_jobs_count', 0)
                  ->orWhereNull('active_jobs_count');
        })
        ->with(['user', 'skills.deviceType'])
        ->get();
    
    echo "<p><strong>Total on-duty technicians with 0 active jobs:</strong> {$technicians->count()}</p>";
    
    if ($technicians->isEmpty()) {
        echo "<p style='color: orange;'><strong>WARNING:</strong> No technicians found with status='on_duty' and active_jobs_count=0</p>";
    } else {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr>";
        echo "<th>ID</th><th>Name</th><th>Status</th><th>Active Jobs</th><th>Skills Count</th><th>Has Laptop Skill</th>";
        echo "</tr>";
        
        foreach ($technicians as $tech) {
            $hasLaptopSkill = false;
            $laptopSkill = null;
            
            foreach ($tech->skills as $skill) {
                if ($skill->deviceType && strtolower($skill->deviceType->name) === 'laptop') {
                    $hasLaptopSkill = true;
                    $laptopSkill = $skill;
                    break;
                }
            }
            
            echo "<tr>";
            echo "<td>{$tech->id}</td>";
            echo "<td>" . ($tech->user->name ?? 'Unknown') . "</td>";
            echo "<td>{$tech->status}</td>";
            echo "<td>{$tech->active_jobs_count}</td>";
            echo "<td>{$tech->skills->count()}</td>";
            echo "<td>" . ($hasLaptopSkill ? "<span style='color: green;'>YES</span> (Level: {$laptopSkill->complexity_level})" : "<span style='color: red;'>NO</span>") . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
    echo "<h2>Recommendation Calculation</h2>";
    
    if (!$ticket->device || !$ticket->device->device_type_id) {
        echo "<p style='color: red;'><strong>CRITICAL ISSUE:</strong> Ticket device does not have device_type_id set!</p>";
        echo "<p>This is why recommendations aren't working. The device needs to have device_type_id set to match technician skills.</p>";
    } else {
        $skillMatchingService = app(\App\Services\Workflow\SkillMatchingService::class);
        $distanceService = app(\App\Services\Location\DistanceCalculationService::class);
        
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr>";
        echo "<th>Technician</th><th>Skill Match Score</th><th>Distance (km)</th><th>Combined Score</th><th>Has Skill</th>";
        echo "</tr>";
        
        foreach ($technicians as $technician) {
            $skillScore = $skillMatchingService->calculateMatchScore($technician, $ticket);
            
            $distanceData = null;
            if ($technician->latitude && $technician->longitude && $ticket->latitude && $ticket->longitude) {
                $distanceData = $distanceService->calculateTechnicianToTicketDistance($technician, $ticket);
            }
            
            $combinedScore = 0;
            if ($distanceData && isset($distanceData['distance_km']) && $distanceData['distance_km'] !== null) {
                $maxDistance = 50;
                $distanceScore = max(0, 100 - (($distanceData['distance_km'] / $maxDistance) * 100));
                $combinedScore = ($skillScore * 0.6) + ($distanceScore * 0.4);
            } else {
                $combinedScore = $skillScore * 0.6;
            }
            
            $deviceTypeId = $ticket->device->device_type_id;
            $technicianSkill = $technician->getSkillForDeviceType($deviceTypeId);
            $hasSkill = $technicianSkill ? true : false;
            
            echo "<tr>";
            echo "<td>" . ($technician->user->name ?? 'Unknown') . " (ID: {$technician->id})</td>";
            echo "<td>" . round($skillScore, 1) . "</td>";
            echo "<td>" . ($distanceData ? round($distanceData['distance_km'] ?? 0, 2) : 'N/A') . "</td>";
            echo "<td>" . round($combinedScore, 1) . "</td>";
            echo "<td>" . ($hasSkill ? "<span style='color: green;'>YES</span>" : "<span style='color: red;'>NO</span>") . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
    echo "<h2>API Endpoint Test</h2>";
    echo "<p>Test the actual API endpoint:</p>";
    echo "<p><a href='/admin/triage/{$ticketId}/recommendations' target='_blank'>/admin/triage/{$ticketId}/recommendations</a></p>";
    echo "<p><em>Note: This requires authentication. Check browser console for errors.</em></p>";
    
} catch (\Exception $e) {
    echo "<p style='color: red;'><strong>ERROR:</strong> " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
