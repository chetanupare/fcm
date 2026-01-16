<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceType;
use App\Models\DeviceBrand;
use App\Models\DeviceModel;
use Illuminate\Http\Request;

/**
 * @tags Public
 * 
 * Device types, brands, and models
 */
class DeviceController extends Controller
{
    /**
     * Get all device types
     */
    public function getDeviceTypes()
    {
        $types = DeviceType::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug', 'icon']);

        return response()->json([
            'device_types' => $types,
        ]);
    }

    /**
     * Get brands for a specific device type
     */
    public function getBrands(Request $request)
    {
        $request->validate([
            'device_type' => 'nullable|string', // slug or name
        ]);

        $query = DeviceBrand::where('is_active', true);

        if ($request->has('device_type')) {
            $deviceType = DeviceType::where('slug', $request->device_type)
                ->orWhere('name', $request->device_type)
                ->first();

            if ($deviceType) {
                // Get brands that have models for this device type
                $brandIds = DeviceModel::where('device_type_id', $deviceType->id)
                    ->where('is_active', true)
                    ->distinct()
                    ->pluck('device_brand_id');

                $query->whereIn('id', $brandIds);
            }
        }

        $brands = $query->orderBy('name')->get(['id', 'name', 'slug', 'origin_country', 'logo_url']);

        return response()->json([
            'brands' => $brands,
        ]);
    }

    /**
     * Get models for a specific device type and brand
     */
    public function getModels(Request $request)
    {
        $request->validate([
            'device_type' => 'required|string', // slug or name
            'brand' => 'required|string', // slug or name or id
        ]);

        $deviceType = DeviceType::where('slug', $request->device_type)
            ->orWhere('name', $request->device_type)
            ->firstOrFail();

        $brand = DeviceBrand::where('slug', $request->brand)
            ->orWhere('name', $request->brand)
            ->orWhere('id', $request->brand)
            ->firstOrFail();

        $models = DeviceModel::where('device_type_id', $deviceType->id)
            ->where('device_brand_id', $brand->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description']);

        return response()->json([
            'models' => $models,
        ]);
    }

    /**
     * Get all device data (types, brands, models) in one call
     */
    public function getAll()
    {
        $types = DeviceType::where('is_active', true)
            ->orderBy('sort_order')
            ->with(['brands' => function ($query) {
                $query->where('is_active', true)->orderBy('name');
            }])
            ->get();

        $data = [];
        foreach ($types as $type) {
            $brands = [];
            $typeModels = DeviceModel::where('device_type_id', $type->id)
                ->where('is_active', true)
                ->with('brand')
                ->get()
                ->groupBy('device_brand_id');

            foreach ($typeModels as $brandId => $models) {
                $brand = DeviceBrand::find($brandId);
                if ($brand && $brand->is_active) {
                    $brands[] = [
                        'id' => $brand->id,
                        'name' => $brand->name,
                        'slug' => $brand->slug,
                        'models' => $models->map(function ($model) {
                            return [
                                'id' => $model->id,
                                'name' => $model->name,
                                'slug' => $model->slug,
                            ];
                        })->values(),
                    ];
                }
            }

            $data[] = [
                'id' => $type->id,
                'name' => $type->name,
                'slug' => $type->slug,
                'icon' => $type->icon,
                'brands' => $brands,
            ];
        }

        return response()->json([
            'device_data' => $data,
        ]);
    }
}
