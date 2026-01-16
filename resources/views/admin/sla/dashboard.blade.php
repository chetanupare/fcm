@extends('layouts.app')

@section('title', 'SLA Dashboard')
@section('page-title', 'SLA Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-2xl font-bold text-slate-800">SLA Compliance Dashboard</h3>
            <p class="text-sm text-slate-500 mt-1">Monitor service level agreements and compliance rates</p>
        </div>
    </div>

    <!-- Compliance Rate Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-sm font-semibold text-slate-600">Triage Compliance</h4>
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-bold text-slate-800">{{ $triageComplianceRate }}%</span>
                <span class="text-sm text-slate-500">on-time</span>
            </div>
            <div class="mt-4 h-2 bg-slate-200 rounded-full overflow-hidden">
                <div class="h-full bg-blue-600 rounded-full transition-all" 
                     style="width: {{ $triageComplianceRate }}%"></div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-sm font-semibold text-slate-600">Assignment Compliance</h4>
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-bold text-slate-800">{{ $assignmentComplianceRate }}%</span>
                <span class="text-sm text-slate-500">on-time</span>
            </div>
            <div class="mt-4 h-2 bg-slate-200 rounded-full overflow-hidden">
                <div class="h-full bg-green-600 rounded-full transition-all" 
                     style="width: {{ $assignmentComplianceRate }}%"></div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-sm font-semibold text-slate-600">Response Compliance</h4>
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-bold text-slate-800">{{ $responseComplianceRate }}%</span>
                <span class="text-sm text-slate-500">on-time</span>
            </div>
            <div class="mt-4 h-2 bg-slate-200 rounded-full overflow-hidden">
                <div class="h-full bg-purple-600 rounded-full transition-all" 
                     style="width: {{ $responseComplianceRate }}%"></div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-sm font-semibold text-slate-600">Resolution Compliance</h4>
                <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-bold text-slate-800">{{ $resolutionComplianceRate }}%</span>
                <span class="text-sm text-slate-500">on-time</span>
            </div>
            <div class="mt-4 h-2 bg-slate-200 rounded-full overflow-hidden">
                <div class="h-full bg-orange-600 rounded-full transition-all" 
                     style="width: {{ $resolutionComplianceRate }}%"></div>
            </div>
        </div>
    </div>

    <!-- At-Risk and Breached Tickets -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <h4 class="text-lg font-bold text-slate-800">At-Risk & Breached Tickets</h4>
            <p class="text-sm text-slate-500 mt-1">Tickets that need immediate attention</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Ticket ID</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Customer</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Priority</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Triage</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Assignment</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Response</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Resolution</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Escalation</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($atRiskTickets as $tracking)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.triage.index') }}" class="text-blue-600 hover:underline font-semibold">
                                    #{{ $tracking->ticket_id }}
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-slate-800">{{ $tracking->ticket->customer->name ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                    {{ $tracking->priority === 'critical' ? 'bg-red-100 text-red-800' : 
                                       ($tracking->priority === 'high' ? 'bg-orange-100 text-orange-800' : 
                                       ($tracking->priority === 'normal' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-800')) }}">
                                    {{ ucfirst($tracking->priority) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                    {{ $tracking->triage_status === 'breached' ? 'bg-red-100 text-red-800' : 
                                       ($tracking->triage_status === 'at_risk' ? 'bg-yellow-100 text-yellow-800' : 
                                       ($tracking->triage_status === 'on_time' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-800')) }}">
                                    {{ ucfirst(str_replace('_', ' ', $tracking->triage_status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                    {{ $tracking->assignment_status === 'breached' ? 'bg-red-100 text-red-800' : 
                                       ($tracking->assignment_status === 'at_risk' ? 'bg-yellow-100 text-yellow-800' : 
                                       ($tracking->assignment_status === 'on_time' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-800')) }}">
                                    {{ ucfirst(str_replace('_', ' ', $tracking->assignment_status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                    {{ $tracking->response_status === 'breached' ? 'bg-red-100 text-red-800' : 
                                       ($tracking->response_status === 'at_risk' ? 'bg-yellow-100 text-yellow-800' : 
                                       ($tracking->response_status === 'on_time' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-800')) }}">
                                    {{ ucfirst(str_replace('_', ' ', $tracking->response_status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                    {{ $tracking->resolution_status === 'breached' ? 'bg-red-100 text-red-800' : 
                                       ($tracking->resolution_status === 'at_risk' ? 'bg-yellow-100 text-yellow-800' : 
                                       ($tracking->resolution_status === 'on_time' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-800')) }}">
                                    {{ ucfirst(str_replace('_', ' ', $tracking->resolution_status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($tracking->escalation_level > 0)
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-red-100 text-red-800">
                                        Level {{ $tracking->escalation_level }}
                                    </span>
                                @else
                                    <span class="text-sm text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-500">
                                No at-risk or breached tickets
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- SLA Configurations -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <h4 class="text-lg font-bold text-slate-800">SLA Configurations</h4>
            <p class="text-sm text-slate-500 mt-1">Current SLA targets by priority level</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Priority</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Triage</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Assignment</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Response</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Resolution</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($slaConfigurations as $config)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-semibold text-slate-800">{{ $config->name }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-slate-600">{{ $config->triage_minutes }} min</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-slate-600">{{ $config->assignment_minutes }} min</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-slate-600">{{ $config->response_minutes }} min</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-slate-600">{{ $config->resolution_minutes }} min</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
