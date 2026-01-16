<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $query = Location::with('manager');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $locations = $query->orderBy('name')->get();

        return response()->json($locations);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:locations,code',
            'address' => 'required|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'manager_name' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
            'type' => 'required|in:head_office,branch,warehouse,service_center',
            'status' => 'sometimes|in:active,inactive,closed',
            'operating_hours' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $location = Location::create($validated);

        return response()->json($location->load('manager'), 201);
    }

    public function show($id)
    {
        $location = Location::with('manager')->findOrFail($id);
        return response()->json($location);
    }

    public function update(Request $request, $id)
    {
        $location = Location::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:50|unique:locations,code,' . $id,
            'address' => 'sometimes|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'manager_name' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
            'type' => 'sometimes|in:head_office,branch,warehouse,service_center',
            'status' => 'sometimes|in:active,inactive,closed',
            'operating_hours' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $location->update($validated);

        return response()->json($location->load('manager'));
    }
}
