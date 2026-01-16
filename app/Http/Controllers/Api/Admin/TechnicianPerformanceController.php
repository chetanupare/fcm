<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\TechnicianPerformance;
use App\Models\Technician;
use App\Models\Job;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TechnicianPerformanceController extends Controller
{
    public function index(Request $request)
    {
        $query = TechnicianPerformance::with('technician.user');

        if ($request->has('technician_id')) {
            $query->where('technician_id', $request->technician_id);
        }

        if ($request->has('period_start')) {
            $query->where('period_start', '>=', $request->period_start);
        }

        if ($request->has('period_end')) {
            $query->where('period_end', '<=', $request->period_end);
        }

        $performance = $query->orderBy('period_start', 'desc')->paginate(20);

        return response()->json($performance);
    }

    public function calculate(Request $request, $technicianId)
    {
        $validated = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
        ]);

        $technician = Technician::findOrFail($technicianId);

        $start = Carbon::parse($validated['period_start']);
        $end = Carbon::parse($validated['period_end']);

        // Get jobs in period
        $jobs = Job::where('technician_id', $technicianId)
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $totalJobs = $jobs->count();
        $completedJobs = $jobs->where('status', 'completed')->count();
        $onTimeCompletions = $jobs->where('status', 'completed')
            ->where('released_at', '<=', $jobs->pluck('ticket.preferred_date')->max())
            ->count();
        $lateCompletions = $completedJobs - $onTimeCompletions;

        // Calculate ratings
        $ratings = $jobs->pluck('ratings')->flatten();
        $averageRating = $ratings->avg('rating') ?? 0;
        $totalRatings = $ratings->count();

        // Calculate revenue
        $revenueGenerated = $jobs->sum(function($job) {
            return $job->payments->sum('amount') ?? 0;
        });

        // Calculate hours (estimated)
        $totalHoursWorked = $jobs->sum(function($job) {
            return $job->checklists->count() * 2; // Rough estimate
        });

        $performance = TechnicianPerformance::updateOrCreate(
            [
                'technician_id' => $technicianId,
                'period_start' => $start,
                'period_end' => $end,
            ],
            [
                'total_jobs' => $totalJobs,
                'completed_jobs' => $completedJobs,
                'on_time_completions' => $onTimeCompletions,
                'late_completions' => $lateCompletions,
                'average_rating' => $averageRating,
                'total_ratings' => $totalRatings,
                'total_hours_worked' => $totalHoursWorked,
                'revenue_generated' => $revenueGenerated,
            ]
        );

        return response()->json($performance->load('technician.user'));
    }

    public function show($id)
    {
        $performance = TechnicianPerformance::with('technician.user')->findOrFail($id);
        return response()->json($performance);
    }
}
