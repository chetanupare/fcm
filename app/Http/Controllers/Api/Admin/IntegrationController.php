<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class IntegrationController extends Controller
{
    public function index(Request $request)
    {
        $query = Integration::query();

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $integrations = $query->orderBy('name')->get();

        return response()->json($integrations);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:accounting,payment,crm,inventory,other',
            'credentials' => 'required|array',
            'settings' => 'nullable|array',
            'mapping' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        // Encrypt credentials
        if (isset($validated['credentials'])) {
            $validated['credentials'] = Crypt::encryptString(json_encode($validated['credentials']));
        }

        $validated['status'] = 'inactive';

        $integration = Integration::create($validated);

        return response()->json($integration, 201);
    }

    public function show($id)
    {
        $integration = Integration::findOrFail($id);
        
        // Decrypt credentials for display (in real app, only show masked values)
        if ($integration->credentials) {
            try {
                $integration->credentials = json_decode(Crypt::decryptString($integration->credentials), true);
            } catch (\Exception $e) {
                $integration->credentials = [];
            }
        }

        return response()->json($integration);
    }

    public function update(Request $request, $id)
    {
        $integration = Integration::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|in:accounting,payment,crm,inventory,other',
            'credentials' => 'sometimes|array',
            'settings' => 'nullable|array',
            'mapping' => 'nullable|array',
            'status' => 'sometimes|in:active,inactive,error',
            'notes' => 'nullable|string',
        ]);

        // Encrypt credentials if provided
        if (isset($validated['credentials'])) {
            $validated['credentials'] = Crypt::encryptString(json_encode($validated['credentials']));
        }

        $integration->update($validated);

        return response()->json($integration);
    }

    public function sync($id)
    {
        $integration = Integration::findOrFail($id);

        if ($integration->status !== 'active') {
            return response()->json(['message' => 'Integration is not active'], 422);
        }

        // TODO: Implement actual sync logic based on integration type
        // This would call the appropriate service (QuickBooks, Xero, etc.)

        $integration->update(['last_sync_at' => now()]);

        return response()->json([
            'message' => 'Sync completed',
            'integration' => $integration,
        ]);
    }
}
