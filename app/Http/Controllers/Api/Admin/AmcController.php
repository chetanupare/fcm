<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AmcContract;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AmcController extends Controller
{
    public function index(Request $request)
    {
        $query = AmcContract::with(['customer', 'device']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->has('expiring_soon')) {
            $query->where('end_date', '<=', now()->addDays(30))
                  ->where('status', 'active');
        }

        $contracts = $query->orderBy('end_date', 'asc')->paginate(20);

        return response()->json($contracts);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'device_id' => 'required|exists:devices,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'duration_type' => 'required|in:monthly,quarterly,semi_annual,annual',
            'contract_amount' => 'required|numeric|min:0',
            'service_charge_per_visit' => 'nullable|numeric|min:0',
            'visits_included' => 'nullable|integer|min:0',
            'terms_and_conditions' => 'nullable|string',
            'covered_services' => 'nullable|array',
            'excluded_services' => 'nullable|array',
            'auto_renew' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['contract_number'] = 'AMC-' . strtoupper(Str::random(8));
        $validated['status'] = 'active';
        $validated['visits_used'] = 0;

        $contract = AmcContract::create($validated);

        return response()->json($contract->load(['customer', 'device']), 201);
    }

    public function show($id)
    {
        $contract = AmcContract::with(['customer', 'device'])->findOrFail($id);
        return response()->json($contract);
    }

    public function update(Request $request, $id)
    {
        $contract = AmcContract::findOrFail($id);

        $validated = $request->validate([
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'duration_type' => 'sometimes|in:monthly,quarterly,semi_annual,annual',
            'contract_amount' => 'sometimes|numeric|min:0',
            'service_charge_per_visit' => 'nullable|numeric|min:0',
            'visits_included' => 'nullable|integer|min:0',
            'status' => 'sometimes|in:active,expired,cancelled,suspended',
            'terms_and_conditions' => 'nullable|string',
            'covered_services' => 'nullable|array',
            'excluded_services' => 'nullable|array',
            'auto_renew' => 'boolean',
            'next_service_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $contract->update($validated);

        return response()->json($contract->load(['customer', 'device']));
    }

    public function recordVisit(Request $request, $id)
    {
        $contract = AmcContract::findOrFail($id);

        if ($contract->visits_included > 0 && $contract->visits_used >= $contract->visits_included) {
            return response()->json(['message' => 'Visit limit reached'], 422);
        }

        $contract->increment('visits_used');
        $contract->update(['last_service_date' => now()]);

        // Calculate next service date based on duration type
        $nextDate = match($contract->duration_type) {
            'monthly' => now()->addMonth(),
            'quarterly' => now()->addMonths(3),
            'semi_annual' => now()->addMonths(6),
            'annual' => now()->addYear(),
            default => now()->addMonth(),
        };
        $contract->update(['next_service_date' => $nextDate]);

        return response()->json($contract);
    }

    public function getExpiringSoon()
    {
        $contracts = AmcContract::where('status', 'active')
            ->where('end_date', '<=', now()->addDays(30))
            ->where('end_date', '>=', now())
            ->with(['customer', 'device'])
            ->orderBy('end_date', 'asc')
            ->get();

        return response()->json($contracts);
    }
}
