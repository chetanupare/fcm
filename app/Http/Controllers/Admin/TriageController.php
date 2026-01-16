<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Technician;
use App\Services\Workflow\AutoAssignService;
use App\Services\Workflow\SkillMatchingService;
use App\Services\Location\DistanceCalculationService;
use Illuminate\Http\Request;

class TriageController extends Controller
{
    public function index()
    {
        $tickets = Ticket::whereIn('status', ['pending_triage', 'triage'])
            ->with(['customer', 'device.deviceType'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($ticket) {
                $countdown = $ticket->triage_deadline_at 
                    ? max(0, $ticket->triage_deadline_at->diffInSeconds(now()))
                    : 0;

                return [
                    'id' => $ticket->id,
                    'customer' => $ticket->customer->name,
                    'device' => $ticket->device->brand . ' ' . $ticket->device->device_type,
                    'device_type_id' => $ticket->device->device_type_id,
                    'issue' => $ticket->issue_description,
                    'status' => $ticket->status,
                    'priority' => $ticket->priority,
                    'countdown' => $countdown,
                    'countdown_formatted' => gmdate('i:s', $countdown),
                    'triage_deadline_at' => $ticket->triage_deadline_at?->toIso8601String(),
                    'created_at' => $ticket->created_at->format('Y-m-d H:i'),
                    'has_location' => $ticket->latitude && $ticket->longitude,
                ];
            });

        // Get assigned tickets with their jobs and technicians
        $assignedTickets = Ticket::whereIn('status', ['assigned', 'in_progress'])
            ->with(['customer', 'device', 'activeJob.technician.user'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($ticket) {
                $job = $ticket->activeJob;
                return [
                    'id' => $ticket->id,
                    'customer' => $ticket->customer->name,
                    'device' => $ticket->device->brand . ' ' . $ticket->device->device_type,
                    'issue' => $ticket->issue_description,
                    'status' => $ticket->status,
                    'priority' => $ticket->priority,
                    'technician' => $job && $job->technician ? $job->technician->user->name : 'Unassigned',
                    'job_status' => $job ? $job->status : null,
                    'distance_km' => $job ? $job->distance_km : null,
                    'estimated_duration' => $job ? $job->estimated_duration_minutes : null,
                    'assigned_at' => $job ? $job->created_at->format('Y-m-d H:i') : null,
                    'created_at' => $ticket->created_at->format('Y-m-d H:i'),
                ];
            });

        $technicians = Technician::with(['user', 'skills.deviceType'])
            ->where('status', 'on_duty')
            ->get()
            ->map(function ($tech) {
                return [
                    'id' => $tech->id,
                    'name' => $tech->user->name,
                    'active_jobs' => $tech->active_jobs_count,
                    'available' => $tech->isAvailable(),
                    'is_on_call' => $tech->is_on_call ?? false,
                    'has_location' => $tech->latitude && $tech->longitude,
                ];
            });

        return view('admin.triage.index', compact('tickets', 'assignedTickets', 'technicians'));
    }

    /**
     * Get recommended technicians for a ticket (with distance and skill scores)
     */
    /**
     * Get recommended technicians for a ticket (with distance and skill scores)
     * 
     * @param int $ticketId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRecommendedTechnicians(int $ticketId)
    {
        try {
            $ticket = Ticket::with('device.deviceType')->findOrFail($ticketId);
            
            // Get available technicians (on duty and not busy)
            // Note: Technicians must have status='on_duty' to appear in recommendations
            // If skilled technicians aren't showing, ensure their status is set to 'on_duty'
            $technicians = Technician::where('status', 'on_duty')
                ->where(function($query) {
                    $query->where('active_jobs_count', 0)
                          ->orWhereNull('active_jobs_count');
                })
                ->with(['user', 'skills.deviceType'])
                ->get();
            
            // Log for debugging
            \Log::info('Recommendations query', [
                'ticket_id' => $ticketId,
                'device_type_id' => $ticket->device->device_type_id ?? null,
                'device_type_name' => $ticket->device->deviceType->name ?? null,
                'technicians_found' => $technicians->count(),
                'technician_ids' => $technicians->pluck('id')->toArray(),
                'technician_details' => $technicians->map(function($tech) {
                    return [
                        'id' => $tech->id,
                        'name' => $tech->user->name ?? 'Unknown',
                        'status' => $tech->status,
                        'active_jobs_count' => $tech->active_jobs_count,
                        'skills_count' => $tech->skills->count(),
                        'skills' => $tech->skills->map(function($skill) {
                            return [
                                'device_type_id' => $skill->device_type_id,
                                'device_type_name' => $skill->deviceType->name ?? null,
                                'is_active' => $skill->is_active,
                            ];
                        })->toArray(),
                    ];
                })->toArray(),
            ]);

            if ($technicians->isEmpty()) {
                return response()->json([
                    'recommendations' => [],
                    'message' => 'No available technicians found',
                ]);
            }

            $skillMatchingService = app(SkillMatchingService::class);
            $distanceService = app(DistanceCalculationService::class);

            $recommendations = $technicians->map(function ($technician) use ($ticket, $skillMatchingService, $distanceService) {
                try {
                    // Calculate skill match score
                    $skillScore = $skillMatchingService->calculateMatchScore($technician, $ticket);
                    
                    // Debug logging for each technician
                    $deviceTypeId = $ticket->device->device_type_id ?? null;
                    $technicianSkill = $deviceTypeId ? $technician->getSkillForDeviceType($deviceTypeId) : null;
                    
                    \Log::info('Technician recommendation calculation', [
                        'technician_id' => $technician->id,
                        'technician_name' => $technician->user->name ?? 'Unknown',
                        'ticket_id' => $ticket->id,
                        'device_type_id' => $deviceTypeId,
                        'has_skill' => $technicianSkill ? true : false,
                        'skill_id' => $technicianSkill->id ?? null,
                        'skill_complexity' => $technicianSkill->complexity_level ?? null,
                        'skill_is_active' => $technicianSkill->is_active ?? null,
                        'skill_match_score' => $skillScore,
                    ]);
                    
                    // Calculate distance
                    $distanceData = null;
                    if ($technician->latitude && $technician->longitude && $ticket->latitude && $ticket->longitude) {
                        $distanceData = $distanceService->calculateTechnicianToTicketDistance($technician, $ticket);
                    }
                    
                    // Combined score
                    $combinedScore = 0;
                    if ($distanceData && isset($distanceData['distance_km']) && $distanceData['distance_km'] !== null) {
                        $maxDistance = 50;
                        $distanceScore = max(0, 100 - (($distanceData['distance_km'] / $maxDistance) * 100));
                        $combinedScore = ($skillScore * 0.6) + ($distanceScore * 0.4);
                    } else {
                        $combinedScore = $skillScore * 0.6;
                    }

                    return [
                        'id' => $technician->id,
                        'name' => $technician->user->name ?? 'Unknown',
                        'skill_match_score' => round($skillScore, 1),
                        'distance_km' => $distanceData ? round($distanceData['distance_km'] ?? 0, 2) : null,
                        'estimated_duration_minutes' => $distanceData ? round($distanceData['duration_minutes'] ?? 0, 1) : null,
                        'combined_score' => round($combinedScore, 1),
                        'has_location' => (bool)($technician->latitude && $technician->longitude),
                        'is_on_call' => (bool)($technician->is_on_call ?? false),
                    ];
                } catch (\Exception $e) {
                    \Log::error('Error calculating recommendation for technician', [
                        'technician_id' => $technician->id,
                        'ticket_id' => $ticket->id,
                        'error' => $e->getMessage(),
                    ]);
                    
                    // Return basic recommendation even if calculation fails
                    return [
                        'id' => $technician->id,
                        'name' => $technician->user->name ?? 'Unknown',
                        'skill_match_score' => 50,
                        'distance_km' => null,
                        'estimated_duration_minutes' => null,
                        'combined_score' => 30,
                        'has_location' => false,
                        'is_on_call' => false,
                    ];
                }
            })->filter(function($rec) {
                return $rec !== null;
            })->sortByDesc('combined_score')->take(5)->values();
            
            // Log final recommendations
            \Log::info('Final recommendations', [
                'ticket_id' => $ticketId,
                'recommendations_count' => $recommendations->count(),
                'recommendations' => $recommendations->map(function($rec) {
                    return [
                        'id' => $rec['id'],
                        'name' => $rec['name'],
                        'skill_match_score' => $rec['skill_match_score'],
                        'combined_score' => $rec['combined_score'],
                    ];
                })->toArray(),
            ]);

            $response = [
                'recommendations' => $recommendations,
                'count' => $recommendations->count(),
                'debug' => [
                    'ticket_id' => $ticketId,
                    'ticket_status' => $ticket->status,
                    'device_id' => $ticket->device_id,
                    'device_type_id' => $ticket->device->device_type_id ?? null,
                    'device_type_name' => $ticket->device->deviceType->name ?? null,
                    'device_type_string' => $ticket->device->device_type ?? null,
                    'technicians_checked' => $technicians->count(),
                    'technicians_with_skills' => $technicians->filter(function($tech) use ($ticket) {
                        if (!$ticket->device || !$ticket->device->device_type_id) {
                            return false;
                        }
                        return $tech->getSkillForDeviceType($ticket->device->device_type_id) !== null;
                    })->count(),
                ],
            ];
            
            // Log the response for debugging
            \Log::info('Recommendations API Response', $response);
            
            return response()->json($response);
        } catch (\Exception $e) {
            \Log::error('Error getting recommended technicians', [
                'ticket_id' => $ticketId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'recommendations' => [],
                'error' => 'Failed to load recommendations: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function assign(Request $request, int $ticketId)
    {
        $request->validate([
            'technician_id' => 'required|exists:technicians,id',
        ]);

        $ticket = Ticket::findOrFail($ticketId);
        $technician = Technician::findOrFail($request->technician_id);

        if (!$technician->isAvailable()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Technician is not available',
                ], 422);
            }
            return back()->withErrors(['technician' => 'Technician is not available']);
        }

        $autoAssignService = app(AutoAssignService::class);
        $assigned = $autoAssignService->assign($ticket);

        if (!$assigned) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Failed to assign technician',
                ], 500);
            }
            return back()->withErrors(['error' => 'Failed to assign technician']);
        }

        // Update SLA tracking
        $slaTrackingService = app(\App\Services\Workflow\SlaTrackingService::class);
        $slaTrackingService->updateSlaStatus($ticket);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Ticket assigned successfully',
            ]);
        }

        return redirect()->route('admin.triage.index')
            ->with('success', 'Ticket assigned successfully');
    }

    public function reject(int $ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);
        $ticket->update(['status' => 'cancelled']);

        return redirect()->route('admin.triage.index')
            ->with('success', 'Ticket rejected');
    }
}
