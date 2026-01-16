<?php

namespace App\Http\Controllers\Api\Technician;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;

/**
 * @tags Technician
 * 
 * Inventory and parts management
 */
class InventoryController extends Controller
{
    public function markOnHold(Request $request, int $jobId)
    {
        $job = Job::where('technician_id', $request->user()->technician->id)
            ->findOrFail($jobId);

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $job->update([
            'status' => 'waiting_parts',
            'notes' => $request->reason ?? 'Waiting for parts',
        ]);

        return response()->json([
            'message' => 'Job marked as waiting for parts',
            'job' => $job->fresh(),
        ]);
    }
}
