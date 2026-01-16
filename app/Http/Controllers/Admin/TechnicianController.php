<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Technician;
use Illuminate\Http\Request;

class TechnicianController extends Controller
{
    public function index()
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
                    'commission_rate' => $tech->commission_rate,
                    'location' => [
                        'latitude' => $tech->latitude,
                        'longitude' => $tech->longitude,
                    ],
                    'last_location_update' => $tech->last_location_update,
                ];
            });

        return view('admin.technicians.index', compact('technicians'));
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
                    'latitude' => $tech->latitude,
                    'longitude' => $tech->longitude,
                ];
            });

        return view('admin.technicians.map', compact('technicians'));
    }

    public function revenue(int $id)
    {
        $technician = Technician::with(['user', 'jobs.payments'])->findOrFail($id);

        $revenue = $technician->jobs()
            ->whereHas('payments', function ($query) {
                $query->where('status', 'completed');
            })
            ->with('payments')
            ->get()
            ->sum(function ($job) {
                return $job->payments->where('status', 'completed')->sum('amount');
            });

        return view('admin.technicians.revenue', compact('technician', 'revenue'));
    }

    public function skills()
    {
        $technicians = Technician::with(['user', 'skills.deviceType', 'primarySkills.deviceType'])
            ->get();

        return view('admin.technicians.skills', compact('technicians'));
    }
}
