<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Technician;
use App\Models\TechnicianSkill;
use App\Models\DeviceType;
use Illuminate\Http\Request;

class TechnicianSkillController extends Controller
{
    /**
     * Get all skills for a technician (for web/Blade views)
     */
    public function index(Request $request, int $technicianId)
    {
        $technician = Technician::findOrFail($technicianId);
        
        $skills = $technician->allSkills()
            ->with('deviceType')
            ->get()
            ->map(function ($skill) {
                return [
                    'id' => $skill->id,
                    'device_type' => $skill->deviceType ? [
                        'id' => $skill->deviceType->id,
                        'name' => $skill->deviceType->name,
                    ] : null,
                    'complexity_level' => $skill->complexity_level,
                    'specialization' => $skill->specialization,
                    'certifications' => $skill->certifications ?? [],
                    'experience_years' => $skill->experience_years,
                    'jobs_completed' => $skill->jobs_completed,
                    'success_rate' => $skill->success_rate,
                    'is_primary' => $skill->is_primary,
                    'is_active' => $skill->is_active,
                    'created_at' => $skill->created_at,
                ];
            });

        return response()->json([
            'technician' => [
                'id' => $technician->id,
                'name' => $technician->user->name,
            ],
            'skills' => $skills,
        ]);
    }

    /**
     * Add a skill to a technician
     */
    public function store(Request $request, int $technicianId)
    {
        $request->validate([
            'device_type_id' => 'required|exists:device_types,id',
            'complexity_level' => 'required|in:basic,intermediate,advanced,expert',
            'specialization' => 'nullable|string|max:255',
            'certifications' => 'nullable|array',
            'certifications.*' => 'string|max:255',
            'experience_years' => 'nullable|integer|min:0',
            'is_primary' => 'nullable|boolean',
        ]);

        $technician = Technician::findOrFail($technicianId);

        // Check if skill already exists
        $existingSkill = TechnicianSkill::where('technician_id', $technicianId)
            ->where('device_type_id', $request->device_type_id)
            ->first();

        if ($existingSkill) {
            return response()->json([
                'message' => 'Skill already exists for this device type',
                'skill' => $existingSkill,
            ], 422);
        }

        $skill = TechnicianSkill::create([
            'technician_id' => $technicianId,
            'device_type_id' => $request->device_type_id,
            'complexity_level' => $request->complexity_level,
            'specialization' => $request->specialization,
            'certifications' => $request->certifications ?? [],
            'experience_years' => $request->experience_years ?? 0,
            'is_primary' => $request->is_primary ?? false,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Skill added successfully',
            'skill' => $skill->load('deviceType'),
        ], 201);
    }

    /**
     * Update a technician skill
     */
    public function update(Request $request, int $technicianId, int $skillId)
    {
        $request->validate([
            'complexity_level' => 'sometimes|in:basic,intermediate,advanced,expert',
            'specialization' => 'nullable|string|max:255',
            'certifications' => 'nullable|array',
            'certifications.*' => 'string|max:255',
            'experience_years' => 'nullable|integer|min:0',
            'is_primary' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $skill = TechnicianSkill::where('technician_id', $technicianId)
            ->findOrFail($skillId);

        $skill->update($request->only([
            'complexity_level',
            'specialization',
            'certifications',
            'experience_years',
            'is_primary',
            'is_active',
        ]));

        return response()->json([
            'message' => 'Skill updated successfully',
            'skill' => $skill->load('deviceType'),
        ]);
    }

    /**
     * Delete a technician skill
     */
    public function destroy(int $technicianId, int $skillId)
    {
        $skill = TechnicianSkill::where('technician_id', $technicianId)
            ->findOrFail($skillId);

        $skill->delete();

        return response()->json([
            'message' => 'Skill deleted successfully',
        ]);
    }

    /**
     * Get available device types for skill assignment
     */
    public function availableDeviceTypes()
    {
        $deviceTypes = DeviceType::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'slug' => $type->slug,
                ];
            });

        return response()->json([
            'device_types' => $deviceTypes,
        ]);
    }
}
