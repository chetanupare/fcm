<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Job;
use App\Models\Technician;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'pending_triage' => Ticket::whereIn('status', ['pending_triage', 'triage'])->count(),
            'active_jobs' => Job::whereNotIn('status', ['completed', 'cancelled'])->count(),
            'on_duty_technicians' => Technician::where('status', 'on_duty')->count(),
            'total_revenue_today' => Payment::whereDate('created_at', today())
                ->where('status', 'completed')
                ->sum('amount'),
            'total_revenue_month' => Payment::whereMonth('created_at', now()->month)
                ->where('status', 'completed')
                ->sum('amount'),
            'total_revenue_all' => Payment::where('status', 'completed')->sum('amount'),
        ];

        $recentTickets = Ticket::with(['customer', 'device'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $recentJobs = Job::with(['ticket.device', 'technician.user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Revenue chart data (last 7 days)
        $revenueChart = Payment::where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(7))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.dashboard', compact('stats', 'recentTickets', 'recentJobs', 'revenueChart'));
    }
}
