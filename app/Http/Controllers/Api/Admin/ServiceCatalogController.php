<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

/**
 * @tags Admin
 * 
 * Service catalog management
 */
class ServiceCatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::query();

        if ($request->has('device_type')) {
            $query->forDeviceType($request->device_type);
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $services = $query->orderBy('name')->get();

        return response()->json([
            'services' => $services,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'device_type' => 'nullable|in:laptop,phone,ac,fridge,tablet,desktop,other,universal',
            'price' => 'required|numeric|min:0',
            'category' => 'required|in:repair,diagnosis,part,visit_fee',
            'is_active' => 'boolean',
        ]);

        $service = Service::create($request->only([
            'name',
            'description',
            'device_type',
            'price',
            'category',
            'is_active',
        ]));

        return response()->json([
            'service' => $service,
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $service = Service::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'device_type' => 'nullable|in:laptop,phone,ac,fridge,tablet,desktop,other,universal',
            'price' => 'sometimes|numeric|min:0',
            'category' => 'sometimes|in:repair,diagnosis,part,visit_fee',
            'is_active' => 'boolean',
        ]);

        $service->update($request->only([
            'name',
            'description',
            'device_type',
            'price',
            'category',
            'is_active',
        ]));

        return response()->json([
            'service' => $service,
        ]);
    }

    public function destroy(int $id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return response()->json([
            'message' => 'Service deleted',
        ]);
    }
}
