<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Technician;
use Illuminate\Http\Request;

/**
 * @tags Admin
 * 
 * Technician management and monitoring
 */
class TechnicianController extends Controller
{
    public function index(Request $request)
    {
        $technicians = Technician::with(['user', 'jobs'])
            ->get()
            ->map(function ($tech) {
                return [
                    'id' => $tech->id,
                    'name' => $tech->user->name,
                    'email' => $tech->user->email,
                    'phone' => $tech->user->phone,
                    'status' => $tech->status,
                    'active_jobs_count' => $tech->active_jobs_count,
                    'total_revenue' => $tech->total_revenue,
                    'location' => [
                        'latitude' => $tech->latitude,
                        'longitude' => $tech->longitude,
                    ],
                    'last_location_update' => $tech->last_location_update,
                ];
            });

        return response()->json([
            'technicians' => $technicians,
        ]);
    }

    public function revenue(int $id)
    {
        $technician = Technician::with(['jobs.payments'])->findOrFail($id);

        $revenue = $technician->jobs()
            ->whereHas('payments', function ($query) {
                $query->where('status', 'completed');
            })
            ->with('payments')
            ->get()
            ->sum(function ($job) {
                return $job->payments->where('status', 'completed')->sum('amount');
            });

        return response()->json([
            'technician' => $technician->load('user'),
            'total_revenue' => $revenue,
            'commission_rate' => $technician->commission_rate,
            'commission_amount' => $revenue * ($technician->commission_rate / 100),
        ]);
    }

    public function map()
    {
        $technicians = Technician::where('status', 'on_duty')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with('user')
            ->get()
            ->map(function ($tech) {
                return [
                    'id' => $tech->id,
                    'name' => $tech->user->name,
                    'status' => $tech->status,
                    'active_jobs_count' => $tech->active_jobs_count,
                    'location' => [
                        'latitude' => $tech->latitude,
                        'longitude' => $tech->longitude,
                    ],
                    'last_update' => $tech->last_location_update,
                ];
            });

        return response()->json([
            'technicians' => $technicians,
        ]);
    }
}
