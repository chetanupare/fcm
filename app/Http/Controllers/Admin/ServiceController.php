<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::orderBy('name')->get();
        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.services.create');
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

        Service::create($request->only([
            'name', 'description', 'device_type', 'price', 'category', 'is_active'
        ]));

        return redirect()->route('admin.services.index')
            ->with('success', 'Service created successfully');
    }

    public function edit(Service $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'device_type' => 'nullable|in:laptop,phone,ac,fridge,tablet,desktop,other,universal',
            'price' => 'required|numeric|min:0',
            'category' => 'required|in:repair,diagnosis,part,visit_fee',
            'is_active' => 'boolean',
        ]);

        $service->update($request->only([
            'name', 'description', 'device_type', 'price', 'category', 'is_active'
        ]));

        return redirect()->route('admin.services.index')
            ->with('success', 'Service updated successfully');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return redirect()->route('admin.services.index')
            ->with('success', 'Service deleted successfully');
    }
}
