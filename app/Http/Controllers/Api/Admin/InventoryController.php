<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Component::with(['category', 'brand', 'supplier', 'location']);

        if ($request->has('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->has('low_stock')) {
            $query->whereColumn('stock_quantity', '<=', 'reorder_level');
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $components = $query->orderBy('name')->paginate(20);

        return response()->json($components);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'location_id' => 'nullable|exists:locations,id',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:components,sku',
            'barcode' => 'nullable|string|max:100|unique:components,barcode',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:component_categories,id',
            'brand_id' => 'nullable|exists:component_brands,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock_level' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'reorder_quantity' => 'required|integer|min:1',
            'unit' => 'required|string|max:50',
            'compatible_devices' => 'nullable|array',
            'compatible_brands' => 'nullable|array',
            'compatible_models' => 'nullable|array',
            'part_number' => 'nullable|string',
            'oem_part_number' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'is_active' => 'boolean',
            'is_consumable' => 'boolean',
        ]);

        // Generate barcode if not provided
        if (empty($validated['barcode'])) {
            $validated['barcode'] = 'BC-' . strtoupper(Str::random(10));
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('components', 'public');
        }

        $component = Component::create($validated);

        return response()->json($component->load(['category', 'brand', 'supplier', 'location']), 201);
    }

    public function show($id)
    {
        $component = Component::with(['category', 'brand', 'supplier', 'location'])->findOrFail($id);
        return response()->json($component);
    }

    public function update(Request $request, $id)
    {
        $component = Component::findOrFail($id);

        $validated = $request->validate([
            'location_id' => 'nullable|exists:locations,id',
            'name' => 'sometimes|string|max:255',
            'sku' => 'sometimes|string|max:100|unique:components,sku,' . $id,
            'barcode' => 'nullable|string|max:100|unique:components,barcode,' . $id,
            'description' => 'nullable|string',
            'category_id' => 'sometimes|exists:component_categories,id',
            'brand_id' => 'nullable|exists:component_brands,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'cost_price' => 'sometimes|numeric|min:0',
            'selling_price' => 'sometimes|numeric|min:0',
            'stock_quantity' => 'sometimes|integer|min:0',
            'min_stock_level' => 'sometimes|integer|min:0',
            'reorder_level' => 'sometimes|integer|min:0',
            'reorder_quantity' => 'sometimes|integer|min:1',
            'unit' => 'sometimes|string|max:50',
            'compatible_devices' => 'nullable|array',
            'compatible_brands' => 'nullable|array',
            'compatible_models' => 'nullable|array',
            'part_number' => 'nullable|string',
            'oem_part_number' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'is_active' => 'boolean',
            'is_consumable' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($component->image) {
                Storage::disk('public')->delete($component->image);
            }
            $validated['image'] = $request->file('image')->store('components', 'public');
        }

        $component->update($validated);

        return response()->json($component->load(['category', 'brand', 'supplier', 'location']));
    }

    public function adjustStock(Request $request, $id)
    {
        $component = Component::findOrFail($id);

        $validated = $request->validate([
            'quantity' => 'required|integer',
            'type' => 'required|in:add,subtract,set',
            'reason' => 'nullable|string',
        ]);

        match($validated['type']) {
            'add' => $component->increment('stock_quantity', $validated['quantity']),
            'subtract' => $component->decrement('stock_quantity', $validated['quantity']),
            'set' => $component->update(['stock_quantity' => $validated['quantity']]),
        };

        // Check if reorder alert needed
        if ($component->shouldSendAlert()) {
            // TODO: Send notification/alert
            $component->update(['alert_sent' => true]);
        }

        return response()->json($component->load(['category', 'brand', 'supplier']));
    }

    public function getReorderAlerts()
    {
        $components = Component::whereColumn('stock_quantity', '<=', 'reorder_level')
            ->where('is_active', true)
            ->where('alert_sent', false)
            ->with(['category', 'brand', 'supplier', 'location'])
            ->get();

        return response()->json($components);
    }

    public function scanBarcode(Request $request)
    {
        $validated = $request->validate([
            'barcode' => 'required|string',
        ]);

        $component = Component::where('barcode', $validated['barcode'])
            ->with(['category', 'brand', 'supplier', 'location'])
            ->first();

        if (!$component) {
            return response()->json(['message' => 'Component not found'], 404);
        }

        return response()->json($component);
    }
}
