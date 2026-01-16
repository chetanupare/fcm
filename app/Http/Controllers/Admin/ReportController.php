<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Job;
use App\Models\Technician;
use App\Models\User;
use App\Models\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function revenue(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'group_by' => 'nullable|in:day,week,month,year',
        ]);

        $startDate = $request->start_date ? Carbon::parse($request->start_date) : now()->subMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : now();
        $groupBy = $request->group_by ?? 'day';

        $query = Payment::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate]);

        $groupFormat = match($groupBy) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => '%Y-%m-%d',
        };

        $revenue = $query->select(
            DB::raw("DATE_FORMAT(created_at, '{$groupFormat}') as period"),
            DB::raw('SUM(amount) as total'),
            DB::raw('SUM(tip_amount) as tips'),
            DB::raw('COUNT(*) as transactions')
        )
        ->groupBy(DB::raw("DATE_FORMAT(created_at, '{$groupFormat}')"))
        ->orderBy('period')
        ->get();

        $summary = [
            'total_revenue' => $query->sum('amount'),
            'total_tips' => $query->sum('tip_amount'),
            'total_transactions' => $query->count(),
            'average_transaction' => $query->avg('amount'),
        ];

        return view('admin.reports.revenue', compact('revenue', 'summary', 'startDate', 'endDate', 'groupBy'));
    }

    public function technician(Request $request)
    {
        $request->validate([
            'technician_id' => 'nullable|exists:technicians,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $startDate = $request->start_date ? Carbon::parse($request->start_date) : now()->subMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : now();

        $query = Job::whereHas('payments', function ($q) use ($startDate, $endDate) {
            $q->where('status', 'completed')
              ->whereBetween('created_at', [$startDate, $endDate]);
        })
        ->with(['technician.user', 'payments']);

        if ($request->technician_id) {
            $query->where('technician_id', $request->technician_id);
        }

        $technicians = Technician::with(['user', 'jobs' => function ($q) use ($startDate, $endDate) {
            $q->whereHas('payments', function ($pq) use ($startDate, $endDate) {
                $pq->where('status', 'completed')
                   ->whereBetween('created_at', [$startDate, $endDate]);
            });
        }])->get();

        $report = $technicians->map(function ($tech) {
            $revenue = $tech->jobs->sum(function ($job) {
                return $job->payments->where('status', 'completed')->sum('amount');
            });
            
            return [
                'technician' => $tech,
                'jobs_count' => $tech->jobs->count(),
                'revenue' => $revenue,
                'commission' => $revenue * ($tech->commission_rate / 100),
            ];
        })->sortByDesc('revenue');

        return view('admin.reports.technician', compact('report', 'startDate', 'endDate'));
    }

    public function component(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $startDate = $request->start_date ? Carbon::parse($request->start_date) : now()->subMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : now();

        $usage = DB::table('component_usage_logs')
            ->join('components', 'component_usage_logs.component_id', '=', 'components.id')
            ->whereBetween('component_usage_logs.created_at', [$startDate, $endDate])
            ->select(
                'components.id',
                'components.name',
                'components.sku',
                DB::raw('SUM(component_usage_logs.quantity) as total_used'),
                DB::raw('SUM(component_usage_logs.unit_cost * component_usage_logs.quantity) as total_cost'),
                DB::raw('COUNT(DISTINCT component_usage_logs.job_id) as jobs_count')
            )
            ->groupBy('components.id', 'components.name', 'components.sku')
            ->orderByDesc('total_used')
            ->get();

        return view('admin.reports.component', compact('usage', 'startDate', 'endDate'));
    }

    public function customer(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $startDate = $request->start_date ? Carbon::parse($request->start_date) : now()->subMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : now();

        $customers = User::where('role', 'customer')
            ->with(['tickets.jobs.payments'])
            ->get()
            ->map(function ($customer) use ($startDate, $endDate) {
                $payments = $customer->tickets->flatMap->jobs->flatMap->payments
                    ->where('status', 'completed')
                    ->filter(function ($payment) use ($startDate, $endDate) {
                        return $payment->created_at >= $startDate && $payment->created_at <= $endDate;
                    });

                return [
                    'customer' => $customer,
                    'jobs_count' => $customer->tickets->flatMap->jobs->count(),
                    'total_spent' => $payments->sum('amount'),
                    'transactions' => $payments->count(),
                ];
            })
            ->sortByDesc('total_spent')
            ->take(50);

        return view('admin.reports.customer', compact('customers', 'startDate', 'endDate'));
    }
}
