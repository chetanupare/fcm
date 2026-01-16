<?php

namespace App\Http\Controllers\Api\Technician;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Checklist;
use Illuminate\Http\Request;

/**
 * @tags Technician
 * 
 * Quality checklist management
 */
class ChecklistController extends Controller
{
    public function index(Request $request, int $jobId)
    {
        $job = Job::where('technician_id', $request->user()->technician->id)
            ->with('ticket.device')
            ->findOrFail($jobId);

        $deviceType = $job->ticket->device->device_type;

        // Get all checklists for this device type
        $checklists = Checklist::forDeviceType($deviceType)
            ->orderBy('order')
            ->get();

        // Get job's checklist completions
        $jobChecklists = $job->checklists()->get()->keyBy('checklist_id');

        $checklists = $checklists->map(function ($checklist) use ($jobChecklists) {
            $jobChecklist = $jobChecklists->get($checklist->id);
            
            return [
                'id' => $checklist->id,
                'name' => $checklist->name,
                'description' => $checklist->description,
                'is_mandatory' => $checklist->is_mandatory,
                'is_completed' => $jobChecklist ? $jobChecklist->is_completed : false,
                'completed_at' => $jobChecklist ? $jobChecklist->completed_at : null,
            ];
        });

        return response()->json([
            'checklists' => $checklists,
        ]);
    }

    public function complete(Request $request, int $jobId, int $checklistId)
    {
        $job = Job::where('technician_id', $request->user()->technician->id)
            ->findOrFail($jobId);

        $checklist = Checklist::findOrFail($checklistId);

        $jobChecklist = $job->checklists()->firstOrCreate(
            ['checklist_id' => $checklistId],
            ['is_completed' => false]
        );

        $jobChecklist->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Checklist item completed',
            'checklist' => $jobChecklist->load('checklist'),
        ]);
    }
}
