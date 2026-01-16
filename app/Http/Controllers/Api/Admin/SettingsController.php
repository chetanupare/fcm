<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

/**
 * @tags Admin
 * 
 * System settings and configuration
 */
class SettingsController extends Controller
{
    public function getWhiteLabel()
    {
        $settings = Setting::where('group', 'white_label')->get()->keyBy('key');

        return response()->json([
            'app_name' => $settings->get('app_name')?->value ?? 'Repair Management',
            'logo_url' => $settings->get('logo_url')?->value ?? null,
            'primary_color' => $settings->get('primary_color')?->value ?? '#3B82F6',
            'secondary_color' => $settings->get('secondary_color')?->value ?? '#1E40AF',
        ]);
    }

    public function updateWhiteLabel(Request $request)
    {
        $request->validate([
            'app_name' => 'sometimes|string|max:255',
            'logo_url' => 'nullable|url',
            'primary_color' => 'sometimes|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'sometimes|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        foreach ($request->only(['app_name', 'logo_url', 'primary_color', 'secondary_color']) as $key => $value) {
            if ($value !== null) {
                Setting::set($key, $value, 'white_label');
            }
        }

        return response()->json([
            'message' => 'White label settings updated',
        ]);
    }

    public function getWorkflow()
    {
        return response()->json([
            'triage_timeout_minutes' => Setting::get('triage_timeout_minutes', 5),
            'job_offer_timeout_minutes' => Setting::get('job_offer_timeout_minutes', 5),
            'require_photos' => Setting::get('require_photos', false),
        ]);
    }

    public function updateWorkflow(Request $request)
    {
        $request->validate([
            'triage_timeout_minutes' => 'sometimes|integer|min:1|max:60',
            'job_offer_timeout_minutes' => 'sometimes|integer|min:1|max:60',
            'require_photos' => 'sometimes|boolean',
        ]);

        foreach ($request->only(['triage_timeout_minutes', 'job_offer_timeout_minutes', 'require_photos']) as $key => $value) {
            if ($value !== null) {
                Setting::set($key, $value, 'workflow');
            }
        }

        return response()->json([
            'message' => 'Workflow settings updated',
        ]);
    }
}
