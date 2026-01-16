<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataRecoveryJob;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DataRecoveryController extends Controller
{
    public function index(Request $request)
    {
        $query = DataRecoveryJob::with(['ticket', 'job', 'technician']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('recovery_type')) {
            $query->where('recovery_type', $request->recovery_type);
        }

        if ($request->has('technician_id')) {
            $query->where('technician_id', $request->technician_id);
        }

        $recoveryJobs = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($recoveryJobs);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'job_id' => 'nullable|exists:service_jobs,id',
            'technician_id' => 'nullable|exists:technicians,id',
            'recovery_type' => 'required|in:hard_drive,ssd,usb,memory_card,phone,tablet,cloud,other',
            'failure_type' => 'nullable|in:logical,physical,corruption,deletion,format,virus,other',
            'estimated_cost' => 'nullable|numeric|min:0',
            'estimated_data_size_gb' => 'nullable|integer|min:0',
            'customer_requirements' => 'nullable|string',
        ]);

        $validated['recovery_number'] = 'DR-' . strtoupper(Str::random(8));
        $validated['status'] = 'received';

        $recoveryJob = DataRecoveryJob::create($validated);

        return response()->json($recoveryJob->load(['ticket', 'job', 'technician']), 201);
    }

    public function show($id)
    {
        $recoveryJob = DataRecoveryJob::with(['ticket', 'job', 'technician'])->findOrFail($id);
        return response()->json($recoveryJob);
    }

    public function update(Request $request, $id)
    {
        $recoveryJob = DataRecoveryJob::findOrFail($id);

        $validated = $request->validate([
            'status' => 'sometimes|in:received,assessment,in_progress,partial_recovery,completed,failed,cancelled',
            'estimated_cost' => 'nullable|numeric|min:0',
            'final_cost' => 'nullable|numeric|min:0',
            'recovered_data_size_gb' => 'nullable|integer|min:0',
            'recovery_percentage' => 'nullable|numeric|min:0|max:100',
            'recovery_notes' => 'nullable|string',
            'recovered_files_list' => 'nullable|array',
            'delivery_method' => 'nullable|string',
            'estimated_completion_date' => 'nullable|date',
            'actual_completion_date' => 'nullable|date',
        ]);

        if (isset($validated['recovered_data_size_gb']) && isset($validated['estimated_data_size_gb'])) {
            $estimated = $recoveryJob->estimated_data_size_gb ?? $validated['estimated_data_size_gb'] ?? 1;
            $recovered = $validated['recovered_data_size_gb'];
            $validated['recovery_percentage'] = ($recovered / $estimated) * 100;
        }

        if (isset($validated['status']) && $validated['status'] === 'completed') {
            $validated['actual_completion_date'] = now();
            $validated['customer_notified'] = false; // Will be notified
        }

        $recoveryJob->update($validated);

        return response()->json($recoveryJob->load(['ticket', 'job', 'technician']));
    }
}
