<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketSlaTracking;
use App\Models\SlaConfiguration;
use Illuminate\Http\Request;

class SlaDashboardController extends Controller
{
    public function index()
    {
        // Get SLA statistics
        $totalTickets = Ticket::count();
        $slaTrackings = TicketSlaTracking::with('ticket')
            ->whereHas('ticket', function ($query) {
                $query->whereNotIn('status', ['completed', 'cancelled', 'closed']);
            })
            ->get();

        // Calculate compliance rates
        $triageCompliant = $slaTrackings->where('triage_status', 'on_time')->count();
        $assignmentCompliant = $slaTrackings->where('assignment_status', 'on_time')->count();
        $responseCompliant = $slaTrackings->where('response_status', 'on_time')->count();
        $resolutionCompliant = $slaTrackings->where('resolution_status', 'on_time')->count();

        $triageComplianceRate = $slaTrackings->count() > 0 
            ? round(($triageCompliant / $slaTrackings->count()) * 100, 1) 
            : 0;

        $assignmentComplianceRate = $slaTrackings->count() > 0 
            ? round(($assignmentCompliant / $slaTrackings->count()) * 100, 1) 
            : 0;

        $responseComplianceRate = $slaTrackings->count() > 0 
            ? round(($responseCompliant / $slaTrackings->count()) * 100, 1) 
            : 0;

        $resolutionComplianceRate = $slaTrackings->count() > 0 
            ? round(($resolutionCompliant / $slaTrackings->count()) * 100, 1) 
            : 0;

        // Get at-risk and breached tickets
        $atRiskTickets = TicketSlaTracking::with(['ticket.customer', 'ticket.device'])
            ->whereHas('ticket', function ($query) {
                $query->whereNotIn('status', ['completed', 'cancelled', 'closed']);
            })
            ->where(function ($query) {
                $query->where('triage_status', 'at_risk')
                    ->orWhere('triage_status', 'breached')
                    ->orWhere('assignment_status', 'at_risk')
                    ->orWhere('assignment_status', 'breached')
                    ->orWhere('response_status', 'at_risk')
                    ->orWhere('response_status', 'breached')
                    ->orWhere('resolution_status', 'at_risk')
                    ->orWhere('resolution_status', 'breached');
            })
            ->orderBy('escalation_level', 'desc')
            ->orderBy('last_escalated_at', 'desc')
            ->get();

        // Get SLA configurations
        $slaConfigurations = SlaConfiguration::where('is_active', true)
            ->orderBy('priority')
            ->get();

        return view('admin.sla.dashboard', compact(
            'totalTickets',
            'triageComplianceRate',
            'assignmentComplianceRate',
            'responseComplianceRate',
            'resolutionComplianceRate',
            'atRiskTickets',
            'slaConfigurations'
        ));
    }
}
