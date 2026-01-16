<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Job;
use App\Models\Technician;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @tags Admin
 * Dashboard and analytics
 */
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
        ];

        $recentTickets = Ticket::with(['customer', 'device'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'stats' => $stats,
            'recent_tickets' => $recentTickets,
        ]);
    }
}
