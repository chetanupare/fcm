<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrandingSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrandingController extends Controller
{
    public function show()
    {
        $branding = BrandingSetting::first();
        
        if (!$branding) {
            // Return default branding
            return response()->json([
                'company_name' => 'Repair Shop',
                'primary_color' => '#3B82F6',
                'secondary_color' => '#8B5CF6',
                'is_active' => true,
            ]);
        }

        return response()->json($branding);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subdomain' => 'nullable|string|max:100|unique:branding_settings,subdomain',
            'company_name' => 'required|string|max:255',
            'logo' => 'nullable|file|mimes:jpg,jpeg,png,svg|max:2048',
            'favicon' => 'nullable|file|mimes:jpg,jpeg,png,ico|max:512',
            'primary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'website' => 'nullable|url',
            'terms_and_conditions' => 'nullable|string',
            'privacy_policy' => 'nullable|string',
            'social_links' => 'nullable|array',
            'custom_fields' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('branding', 'public');
        }

        if ($request->hasFile('favicon')) {
            $validated['favicon'] = $request->file('favicon')->store('branding', 'public');
        }

        $branding = BrandingSetting::updateOrCreate(
            ['id' => 1], // Single branding setting
            $validated
        );

        return response()->json($branding, 201);
    }

    public function update(Request $request)
    {
        $branding = BrandingSetting::firstOrCreate(['id' => 1]);

        $validated = $request->validate([
            'subdomain' => 'nullable|string|max:100|unique:branding_settings,subdomain,' . $branding->id,
            'company_name' => 'sometimes|string|max:255',
            'logo' => 'nullable|file|mimes:jpg,jpeg,png,svg|max:2048',
            'favicon' => 'nullable|file|mimes:jpg,jpeg,png,ico|max:512',
            'primary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'website' => 'nullable|url',
            'terms_and_conditions' => 'nullable|string',
            'privacy_policy' => 'nullable|string',
            'social_links' => 'nullable|array',
            'custom_fields' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('logo')) {
            if ($branding->logo) {
                Storage::disk('public')->delete($branding->logo);
            }
            $validated['logo'] = $request->file('logo')->store('branding', 'public');
        }

        if ($request->hasFile('favicon')) {
            if ($branding->favicon) {
                Storage::disk('public')->delete($branding->favicon);
            }
            $validated['favicon'] = $request->file('favicon')->store('branding', 'public');
        }

        $branding->update($validated);

        return response()->json($branding);
    }
}
