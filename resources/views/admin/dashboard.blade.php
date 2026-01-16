@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-4 md:space-y-6">
    <!-- Hero Stats - The "5-Minute Timer" Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
        <!-- Pending Triage - Urgent Card -->
        <div class="relative overflow-hidden rounded-xl bg-white p-3 md:p-4 shadow-lg border border-orange-100 group hover:shadow-xl transition-all duration-300 ticket-enter {{ $stats['pending_triage'] > 0 ? 'timer-urgent' : '' }}">
            <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-pulse-orange opacity-10 blur-xl {{ $stats['pending_triage'] > 0 ? 'animate-pulse' : '' }}"></div>
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <p class="text-xs md:text-sm font-medium text-slate-500 truncate">Pending Triage</p>
                    <h3 class="mt-1 text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight">
                        {{ str_pad($stats['pending_triage'], 2, '0', STR_PAD_LEFT) }}
                        @if($stats['pending_triage'] > 0)
                            <span class="text-xs md:text-sm font-normal text-pulse-orange ml-1 hidden md:inline">Action</span>
                        @endif
                    </h3>
                </div>
                <div class="relative h-10 w-10 md:h-12 md:w-12 flex items-center justify-center flex-shrink-0 ml-2">
                    <svg class="h-full w-full rotate-90 transform text-orange-100" viewBox="0 0 36 36">
                        <path class="text-gray-100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3" />
                        @if($stats['pending_triage'] > 0)
                            <path class="text-pulse-orange animate-[dash_5s_linear_forwards]" stroke-dasharray="100, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3" />
                        @endif
                    </svg>
                    @if($stats['pending_triage'] > 0)
                        <span class="absolute text-[10px] md:text-xs font-bold text-pulse-orange">4:59</span>
                    @endif
                </div>
            </div>
            <a href="{{ route('admin.triage.index') }}" class="mt-2 md:mt-3 inline-flex items-center text-xs md:text-sm font-medium text-pulse-orange hover:text-pulse-orange/80">
                View Queue
                <svg class="ml-1 w-3 h-3 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>

        <!-- Active Jobs -->
        <div class="relative overflow-hidden rounded-xl bg-white p-3 md:p-4 shadow-lg border border-slate-100 group hover:shadow-xl transition-all duration-300 ticket-enter">
            <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-blue-500 opacity-5 blur-xl"></div>
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <p class="text-xs md:text-sm font-medium text-slate-500 truncate">Active Jobs</p>
                    <h3 class="mt-1 text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight">{{ str_pad($stats['active_jobs'], 2, '0', STR_PAD_LEFT) }}</h3>
                    <p class="mt-0.5 text-[10px] md:text-xs text-slate-400">In Progress</p>
                </div>
                <div class="h-10 w-10 md:h-12 md:w-12 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-md flex-shrink-0 ml-2">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- On Duty Technicians -->
        <div class="relative overflow-hidden rounded-xl bg-white p-3 md:p-4 shadow-lg border border-slate-100 group hover:shadow-xl transition-all duration-300 ticket-enter">
            <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-purple-500 opacity-5 blur-xl"></div>
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <p class="text-xs md:text-sm font-medium text-slate-500 truncate">On Duty Techs</p>
                    <h3 class="mt-1 text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight">{{ str_pad($stats['on_duty_technicians'], 2, '0', STR_PAD_LEFT) }}</h3>
                    <p class="mt-0.5 text-[10px] md:text-xs text-slate-400">Available</p>
                </div>
                <div class="h-10 w-10 md:h-12 md:w-12 rounded-lg bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center shadow-md flex-shrink-0 ml-2">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Today's Revenue -->
        <div class="relative overflow-hidden rounded-xl bg-white p-3 md:p-4 shadow-lg border border-slate-100 group hover:shadow-xl transition-all duration-300 ticket-enter">
            <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-yellow-500 opacity-5 blur-xl"></div>
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <p class="text-xs md:text-sm font-medium text-slate-500 truncate">Today's Revenue</p>
                    <h3 class="mt-1 text-xl md:text-2xl font-extrabold text-slate-800 tracking-tight truncate">@currency($stats['total_revenue_today'])</h3>
                    <p class="mt-0.5 text-[10px] md:text-xs text-slate-400 truncate">Month: @currency($stats['total_revenue_month'])</p>
                </div>
                <div class="h-10 w-10 md:h-12 md:w-12 rounded-lg bg-gradient-to-br from-yellow-500 to-yellow-600 flex items-center justify-center shadow-md flex-shrink-0 ml-2">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="bg-white rounded-xl shadow-lg border border-slate-100 p-4 md:p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
            <h3 class="text-base md:text-lg font-bold text-slate-800">Revenue Trend (Last 7 Days)</h3>
            <div class="flex items-center gap-2 text-xs md:text-sm text-slate-500">
                <div class="h-2 w-2 md:h-3 md:w-3 rounded-full bg-blue-500"></div>
                <span>Daily Revenue</span>
            </div>
        </div>
        <div class="relative" style="height: 200px;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Recent Activity - Floating Cards -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 md:gap-5">
        <!-- Recent Tickets -->
        <div class="bg-white rounded-xl shadow-lg border border-slate-100 overflow-hidden">
            <div class="px-4 md:px-5 py-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white">
                <h3 class="text-base md:text-lg font-bold text-slate-800">Recent Tickets</h3>
            </div>
            <div class="p-3 md:p-4 space-y-2 max-h-[400px] overflow-y-auto">
                @forelse($recentTickets as $ticket)
                    <div class="group relative flex items-center gap-2 md:gap-3 bg-slate-50 p-2 md:p-3 rounded-lg border border-slate-100 shadow-sm hover:shadow-md hover:border-blue-200 transition-all duration-200 cursor-pointer ticket-enter">
                        <div class="absolute left-0 top-1 bottom-1 w-0.5 md:w-1 bg-blue-500 rounded-r-lg opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="h-8 w-8 md:h-10 md:w-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 flex-shrink-0">
                            <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-slate-800 text-xs md:text-sm truncate">#{{ $ticket->id }} - {{ $ticket->customer->name }}</p>
                            <p class="text-[10px] md:text-xs text-slate-500 truncate">{{ $ticket->device->brand }} {{ $ticket->device->device_type }}</p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="inline-flex items-center px-1.5 md:px-2 py-0.5 rounded-full text-[10px] md:text-xs font-medium border shadow-sm
                                {{ $ticket->status === 'pending_triage' ? 'bg-yellow-100 text-yellow-800 border-yellow-200' : 
                                   ($ticket->status === 'completed' ? 'bg-green-100 text-green-800 border-green-200' : 'bg-blue-100 text-blue-800 border-blue-200') }}">
                                @if($ticket->status === 'pending_triage')
                                    <span class="w-1 h-1 md:w-1.5 md:h-1.5 rounded-full bg-yellow-500 mr-1 animate-pulse"></span>
                                @endif
                                <span class="hidden md:inline">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
                                <span class="md:hidden">{{ ucfirst(explode('_', $ticket->status)[0]) }}</span>
                            </span>
                            <button class="text-slate-400 hover:text-blue-600 transition-colors">
                                <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="text-xs md:text-sm text-slate-500 text-center py-6">No recent tickets</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Jobs -->
        <div class="bg-white rounded-xl shadow-lg border border-slate-100 overflow-hidden">
            <div class="px-4 md:px-5 py-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white">
                <h3 class="text-base md:text-lg font-bold text-slate-800">Recent Jobs</h3>
            </div>
            <div class="p-3 md:p-4 space-y-2 max-h-[400px] overflow-y-auto">
                @forelse($recentJobs as $job)
                    <div class="group relative flex items-center gap-2 md:gap-3 bg-slate-50 p-2 md:p-3 rounded-lg border border-slate-100 shadow-sm hover:shadow-md hover:border-blue-200 transition-all duration-200 cursor-pointer ticket-enter">
                        <div class="absolute left-0 top-1 bottom-1 w-0.5 md:w-1 bg-blue-500 rounded-r-lg opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="h-8 w-8 md:h-10 md:w-10 rounded-lg bg-purple-50 flex items-center justify-center text-purple-600 flex-shrink-0">
                            <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-slate-800 text-xs md:text-sm truncate">Job #{{ $job->id }}</p>
                            <p class="text-[10px] md:text-xs text-slate-500 truncate">{{ $job->technician->user->name ?? 'N/A' }} - {{ $job->ticket->device->brand }}</p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="inline-flex items-center px-1.5 md:px-2 py-0.5 rounded-full text-[10px] md:text-xs font-medium border shadow-sm
                                {{ $job->status === 'completed' ? 'bg-green-100 text-green-800 border-green-200' : 
                                   ($job->status === 'cancelled' ? 'bg-red-100 text-red-800 border-red-200' : 'bg-blue-100 text-blue-800 border-blue-200') }}">
                                @if($job->status === 'in_progress' || $job->status === 'repairing')
                                    <span class="w-1 h-1 md:w-1.5 md:h-1.5 rounded-full bg-blue-500 mr-1 animate-pulse"></span>
                                @endif
                                <span class="hidden md:inline">{{ ucfirst(str_replace('_', ' ', $job->status)) }}</span>
                                <span class="md:hidden">{{ ucfirst(explode('_', $job->status)[0]) }}</span>
                            </span>
                            <button class="text-slate-400 hover:text-blue-600 transition-colors">
                                <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="text-xs md:text-sm text-slate-500 text-center py-6">No recent jobs</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const revenueData = @json($revenueChart);
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: revenueData.map(d => new Date(d.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })),
            datasets: [{
                label: 'Revenue ($)',
                data: revenueData.map(d => parseFloat(d.total)),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true,
                borderWidth: 3,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: 'rgb(59, 130, 246)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        callback: function(value) {
                            return '$' + value;
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
</script>
@endpush
