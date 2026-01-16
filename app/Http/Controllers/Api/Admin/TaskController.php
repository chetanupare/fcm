<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with(['creator', 'assignee', 'job', 'ticket']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('overdue')) {
            $query->where('due_date', '<', now()->toDateString())
                  ->whereNotIn('status', ['completed', 'cancelled']);
        }

        $tasks = $query->orderBy('due_date', 'asc')->paginate(20);

        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
            'job_id' => 'nullable|exists:service_jobs,id',
            'ticket_id' => 'nullable|exists:tickets,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|integer|min:0',
            'tags' => 'nullable|array',
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['status'] = 'pending';

        $task = Task::create($validated);

        return response()->json($task->load(['creator', 'assignee', 'job', 'ticket']), 201);
    }

    public function show($id)
    {
        $task = Task::with(['creator', 'assignee', 'job', 'ticket'])->findOrFail($id);
        return response()->json($task);
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $validated = $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'sometimes|in:low,normal,high,urgent',
            'status' => 'sometimes|in:pending,in_progress,completed,cancelled,on_hold',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|integer|min:0',
            'actual_hours' => 'nullable|integer|min:0',
            'tags' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        if (isset($validated['status'])) {
            if ($validated['status'] === 'in_progress' && !$task->started_at) {
                $validated['started_at'] = now();
            }
            if ($validated['status'] === 'completed' && !$task->completed_at) {
                $validated['completed_at'] = now();
            }
        }

        $task->update($validated);

        return response()->json($task->load(['creator', 'assignee', 'job', 'ticket']));
    }

    public function start($id)
    {
        $task = Task::findOrFail($id);
        $task->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
        return response()->json($task);
    }

    public function complete(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $validated = $request->validate([
            'actual_hours' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
            'actual_hours' => $validated['actual_hours'] ?? $task->actual_hours,
            'notes' => $validated['notes'] ?? $task->notes,
        ]);

        return response()->json($task->load(['creator', 'assignee', 'job', 'ticket']));
    }
}
