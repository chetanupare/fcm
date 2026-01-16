<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Component;
use App\Models\ComponentCategory;
use App\Models\ComponentBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ComponentController extends Controller
{
    public function index(Request $request)
    {
        $query = Component::with(['category', 'brand']);

        // Filters
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('brand_id') && $request->brand_id) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('part_number', 'like', "%{$search}%");
            });
        }

        if ($request->has('low_stock') && $request->low_stock) {
            $query->whereRaw('stock_quantity <= min_stock_level');
        }

        $components = $query->orderBy('created_at', 'desc')->paginate(20);
        $categories = ComponentCategory::where('is_active', true)->orderBy('name')->get();
        $brands = ComponentBrand::where('is_active', true)->orderBy('name')->get();

        // Statistics
        $stats = [
            'total' => Component::count(),
            'active' => Component::where('is_active', true)->count(),
            'low_stock' => Component::whereRaw('stock_quantity <= min_stock_level')->count(),
            'total_value' => Component::get()->sum(function($c) { return $c->stock_quantity * $c->cost_price; }),
        ];

        return view('admin.components.index', compact('components', 'categories', 'brands', 'stats'));
    }

    public function create()
    {
        $categories = ComponentCategory::where('is_active', true)->orderBy('name')->get();
        $brands = ComponentBrand::where('is_active', true)->orderBy('name')->get();
        return view('admin.components.create', compact('categories', 'brands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:components,sku',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:component_categories,id',
            'brand_id' => 'nullable|exists:component_brands,id',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock_level' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'compatible_devices' => 'nullable|array',
            'compatible_brands' => 'nullable|array',
            'compatible_models' => 'nullable|array',
            'part_number' => 'nullable|string|max:100',
            'oem_part_number' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'is_consumable' => 'boolean',
        ]);

               $component = Component::create($validated);
               
               // Log activity
               ActivityLog::log('created', $component);

               return redirect()->route('admin.components.index')
                   ->with('success', 'Component created successfully');
    }

    public function show($id)
    {
        $component = Component::with(['category', 'brand'])->findOrFail($id);
        
        // Usage trends (last 30 days)
        $usageTrends = \DB::table('component_usage_logs')
            ->where('component_id', $id)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, SUM(quantity) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.components.show', compact('component', 'usageTrends'));
    }

    public function edit($id)
    {
        $component = Component::findOrFail($id);
        $categories = ComponentCategory::where('is_active', true)->orderBy('name')->get();
        $brands = ComponentBrand::where('is_active', true)->orderBy('name')->get();
        return view('admin.components.edit', compact('component', 'categories', 'brands'));
    }

    public function update(Request $request, $id)
    {
        $component = Component::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:components,sku,' . $id,
            'description' => 'nullable|string',
            'category_id' => 'required|exists:component_categories,id',
            'brand_id' => 'nullable|exists:component_brands,id',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock_level' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'compatible_devices' => 'nullable|array',
            'compatible_brands' => 'nullable|array',
            'compatible_models' => 'nullable|array',
            'part_number' => 'nullable|string|max:100',
            'oem_part_number' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'is_consumable' => 'boolean',
        ]);

               $oldValues = $component->only(array_keys($validated));
               $component->update($validated);
               
               // Log activity
               ActivityLog::log('updated', $component, $oldValues, $component->fresh()->only(array_keys($validated)));

               return redirect()->route('admin.components.show', $component)
                   ->with('success', 'Component updated successfully');
    }

    public function destroy($id)
    {
        $component = Component::findOrFail($id);
        $component->delete();

        return redirect()->route('admin.components.index')
            ->with('success', 'Component deleted successfully');
    }

    public function trends()
    {
        // Component requirement trends
        $trends = Component::with(['category', 'brand'])
            ->where('created_at', '>=', now()->subDays(90))
            ->orderBy('total_used', 'desc')
            ->limit(20)
            ->get()
            ->map(function($component) {
                $usageCount = DB::table('component_usage_logs')
                    ->where('component_id', $component->id)
                    ->count();
                $component->usage_count = $usageCount;
                return $component;
            });

        $categories = ComponentCategory::withCount('components')->get();
        $brands = ComponentBrand::withCount('components')->get();

        return view('admin.components.trends', compact('trends', 'categories', 'brands'));
    }

    public function categories()
    {
        $categories = ComponentCategory::withCount('components')->orderBy('name')->get();
        return view('admin.components.categories', compact('categories'));
    }

    public function brands()
    {
        $brands = ComponentBrand::withCount('components')->orderBy('name')->get();
        return view('admin.components.brands', compact('brands'));
    }
}
