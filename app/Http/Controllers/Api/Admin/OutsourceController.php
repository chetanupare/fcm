<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\OutsourceVendor;
use App\Models\OutsourceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OutsourceController extends Controller
{
    // Vendor Management
    public function vendors(Request $request)
    {
        $query = OutsourceVendor::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $vendors = $query->orderBy('name')->paginate(20);

        return response()->json($vendors);
    }

    public function storeVendor(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'required|string',
            'address' => 'nullable|string',
            'specialization' => 'nullable|string',
            'services_offered' => 'nullable|array',
            'status' => 'sometimes|in:active,inactive,suspended',
            'notes' => 'nullable|string',
        ]);

        $vendor = OutsourceVendor::create($validated);

        return response()->json($vendor, 201);
    }

    public function updateVendor(Request $request, $id)
    {
        $vendor = OutsourceVendor::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'sometimes|string',
            'address' => 'nullable|string',
            'specialization' => 'nullable|string',
            'services_offered' => 'nullable|array',
            'status' => 'sometimes|in:active,inactive,suspended',
            'rating' => 'nullable|numeric|min:0|max:5',
            'notes' => 'nullable|string',
        ]);

        $vendor->update($validated);

        return response()->json($vendor);
    }

    // Request Management
    public function requests(Request $request)
    {
        $query = OutsourceRequest::with(['vendor', 'job', 'ticket', 'creator']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($requests);
    }

    public function storeRequest(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:outsource_vendors,id',
            'job_id' => 'nullable|exists:service_jobs,id',
            'ticket_id' => 'nullable|exists:tickets,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'requested_date' => 'nullable|date',
            'internal_notes' => 'nullable|string',
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['request_number'] = 'OSR-' . strtoupper(Str::random(8));
        $validated['status'] = 'pending';

        $outsourceRequest = OutsourceRequest::create($validated);

        return response()->json($outsourceRequest->load(['vendor', 'job', 'ticket', 'creator']), 201);
    }

    public function updateRequest(Request $request, $id)
    {
        $outsourceRequest = OutsourceRequest::findOrFail($id);

        $validated = $request->validate([
            'status' => 'sometimes|in:pending,sent,accepted,in_progress,completed,rejected,cancelled',
            'quoted_amount' => 'nullable|numeric|min:0',
            'final_amount' => 'nullable|numeric|min:0',
            'completion_date' => 'nullable|date',
            'vendor_notes' => 'nullable|string',
            'internal_notes' => 'nullable|string',
        ]);

        $outsourceRequest->update($validated);

        return response()->json($outsourceRequest->load(['vendor', 'job', 'ticket', 'creator']));
    }
}
