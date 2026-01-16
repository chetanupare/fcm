@extends('layouts.app')

@section('title', 'Triage Queue')
@section('page-title', 'Triage Queue')

@section('content')
<div class="space-y-6" x-data="{ 
    assignModalOpen: false, 
    selectedTicket: null,
    recommendations: [],
    loadingRecommendations: false,
    selectedTechnician: null
}">
    <!-- Header with Stats -->
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-2xl font-bold text-slate-800">Pending Tickets</h3>
            <p class="text-sm text-slate-500 mt-1">Monitor and assign incoming repair requests</p>
        </div>
        <div class="flex items-center gap-4">
            <div class="px-4 py-2 bg-white rounded-xl border border-slate-200 shadow-sm">
                <span class="text-xs text-slate-500">Total Pending</span>
                <p class="text-2xl font-bold text-slate-800">{{ count($tickets) }}</p>
            </div>
        </div>
    </div>

    <!-- Tickets Grid - Floating Cards -->
    <div class="space-y-4">
        @forelse($tickets as $ticket)
            <div class="group relative bg-white rounded-2xl border border-slate-100 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden ticket-enter {{ $ticket['priority'] === 'high' ? 'border-l-4 border-l-pulse-orange' : '' }}">
                <!-- Urgent Pulse Effect -->
                @if($ticket['countdown'] < 60 && $ticket['status'] === 'pending_triage')
                    <div class="absolute inset-0 timer-urgent pointer-events-none"></div>
                @endif
                
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-blue-500 to-blue-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                
                <div class="p-6">
                    <div class="grid grid-cols-12 gap-6 items-center">
                        <!-- Customer & Device Info -->
                        <div class="col-span-4 flex items-center gap-4">
                            <div class="h-14 w-14 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white shadow-lg">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-bold text-slate-800 text-lg">Ticket #{{ $ticket['id'] }}</p>
                                <p class="text-sm text-slate-600 mt-1">{{ $ticket['customer'] }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $ticket['device'] }}</p>
                            </div>
                        </div>

                        <!-- Issue Description -->
                        <div class="col-span-3">
                            <p class="text-sm text-slate-700 line-clamp-2">{{ Str::limit($ticket['issue'], 80) }}</p>
                        </div>

                        <!-- Priority Badge -->
                        <div class="col-span-1">
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold border shadow-sm
                                {{ $ticket['priority'] === 'high' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-slate-50 text-slate-600 border-slate-200' }}">
                                @if($ticket['priority'] === 'high')
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5 animate-pulse"></span>
                                @endif
                                {{ ucfirst($ticket['priority']) }}
                            </span>
                        </div>

                        <!-- Countdown Timer -->
                        <div class="col-span-2">
                            @if($ticket['triage_deadline_at'])
                                <div class="flex items-center gap-3" data-ticket-id="{{ $ticket['id'] }}" data-deadline="{{ $ticket['triage_deadline_at'] }}">
                                    <div class="relative h-12 w-12">
                                        <svg class="h-full w-full rotate-90 transform text-slate-100" viewBox="0 0 36 36">
                                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3" />
                                            <path class="countdown-progress text-pulse-orange" stroke-dasharray="100, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3" />
                                        </svg>
                                        <span class="countdown-text absolute inset-0 flex items-center justify-center text-xs font-bold {{ $ticket['countdown'] < 60 ? 'text-pulse-orange' : 'text-slate-600' }}">
                                            {{ $ticket['countdown_formatted'] }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-500">Time Remaining</p>
                                        <p class="text-sm font-semibold text-slate-800">Auto-assign soon</p>
                                    </div>
                                </div>
                            @else
                                <span class="text-xs text-slate-400">No deadline</span>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="col-span-2 flex items-center justify-end gap-2">
                            <button type="button"
                                    @click.stop="window.openAssignModal({{ $ticket['id'] }})"
                                    data-ticket-id="{{ $ticket['id'] }}"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm shadow-sm hover:shadow-md">
                                Assign
                            </button>
                            <form method="POST" action="{{ route('admin.triage.reject', $ticket['id']) }}" class="inline">
                                @csrf
                                <button type="submit" onclick="return confirm('Reject this ticket?')" 
                                        class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors font-medium text-sm">
                                    Reject
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-100 shadow-lg p-12 text-center">
                <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-slate-500 text-lg font-medium">No pending tickets</p>
                <p class="text-slate-400 text-sm mt-1">All tickets have been processed</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Assigned Tasks Table -->
<div class="mt-8">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-2xl font-bold text-slate-800">Assigned Tasks</h3>
            <p class="text-sm text-slate-500 mt-1">Monitor assigned and in-progress tickets</p>
        </div>
        <div class="px-4 py-2 bg-white rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs text-slate-500">Total Assigned</span>
            <p class="text-2xl font-bold text-slate-800">{{ count($assignedTickets) }}</p>
        </div>
    </div>

    @if(count($assignedTickets) > 0)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Ticket ID</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Device</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Issue</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Technician</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Job Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Ticket Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Assigned At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($assignedTickets as $ticket)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-semibold text-slate-800">#{{ $ticket['id'] }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-slate-800">{{ $ticket['customer'] }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-slate-600">{{ $ticket['device'] }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-slate-600 line-clamp-1">{{ Str::limit($ticket['issue'], 50) }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm font-medium text-slate-800">{{ $ticket['technician'] }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($ticket['job_status'])
                                        @php
                                            $statusColors = [
                                                'offered' => 'bg-yellow-100 text-yellow-800',
                                                'accepted' => 'bg-blue-100 text-blue-800',
                                                'en_route' => 'bg-purple-100 text-purple-800',
                                                'component_pickup' => 'bg-violet-100 text-violet-800',
                                                'arrived' => 'bg-indigo-100 text-indigo-800',
                                                'diagnosing' => 'bg-yellow-100 text-yellow-800',
                                                'quoted' => 'bg-teal-100 text-teal-800',
                                                'signed_contract' => 'bg-emerald-100 text-emerald-800',
                                                'repairing' => 'bg-orange-100 text-orange-800',
                                                'waiting_parts' => 'bg-amber-100 text-amber-800',
                                                'quality_check' => 'bg-cyan-100 text-cyan-800',
                                                'waiting_payment' => 'bg-pink-100 text-pink-800',
                                                'completed' => 'bg-green-100 text-green-800',
                                                'released' => 'bg-slate-100 text-slate-800',
                                            ];
                                            $statusLabels = [
                                                'offered' => 'Offered',
                                                'accepted' => 'Accepted',
                                                'en_route' => 'On My Way',
                                                'component_pickup' => 'Component Pickup',
                                                'arrived' => 'Reached',
                                                'diagnosing' => 'Diagnosed',
                                                'quoted' => 'Quoted',
                                                'signed_contract' => 'Signed Contract',
                                                'repairing' => 'Fixing',
                                                'waiting_parts' => 'Waiting for Parts',
                                                'quality_check' => 'Quality Check',
                                                'waiting_payment' => 'Waiting for Payment',
                                                'completed' => 'Completed',
                                                'released' => 'Released',
                                            ];
                                            $color = $statusColors[$ticket['job_status']] ?? 'bg-slate-100 text-slate-800';
                                            $label = $statusLabels[$ticket['job_status']] ?? ucfirst(str_replace('_', ' ', $ticket['job_status']));
                                        @endphp
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $color }}">
                                            {{ $label }}
                                        </span>
                                    @else
                                        <span class="text-sm text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                        {{ $ticket['status'] === 'in_progress' ? 'bg-indigo-100 text-indigo-800' : 
                                           'bg-purple-100 text-purple-800' }}">
                                        {{ ucfirst(str_replace('_', ' ', $ticket['status'])) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-slate-600">{{ $ticket['assigned_at'] ?? '-' }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white rounded-2xl border border-slate-100 shadow-lg p-12 text-center">
            <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            <p class="text-slate-500 text-lg font-medium">No assigned tasks</p>
            <p class="text-slate-400 text-sm mt-1">All tickets are pending assignment</p>
        </div>
    @endif
</div>

<!-- Assign Modal - Enhanced with Recommendations -->
<div x-show="assignModalOpen" 
     x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click.away="assignModalOpen = false"
     @keydown.escape.window="assignModalOpen = false"
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     id="assign-modal-overlay">
    <div @click.stop class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-6 transform transition-all ticket-enter max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-slate-800">Assign Technician</h3>
            <button type="button" 
                    @click.stop="assignModalOpen = false; window.closeAssignModal && window.closeAssignModal();"
                    onclick="event.preventDefault(); event.stopPropagation(); window.closeAssignModal && window.closeAssignModal(); return false;"
                    class="text-slate-400 hover:text-slate-600 transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 rounded">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <!-- Recommended Technicians -->
        <div x-show="recommendations && recommendations.length > 0" class="mb-6">
            <h4 class="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Recommended Technicians (Top Matches)
            </h4>
            <div class="space-y-2">
                <template x-for="(rec, index) in recommendations" :key="rec.id">
                    <div @click="selectedTechnician = rec.id; document.getElementById('technician-select').value = rec.id"
                         :class="selectedTechnician === rec.id ? 'border-blue-500 bg-blue-50' : 'border-slate-200 hover:border-blue-300 hover:bg-slate-50'"
                         class="border rounded-lg p-3 cursor-pointer transition-all">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-bold text-slate-400" x-text="'#' + (index + 1)"></span>
                                    <span class="font-semibold text-slate-800" x-text="rec.name"></span>
                                    <span x-show="rec.is_on_call" class="text-xs px-2 py-0.5 bg-orange-100 text-orange-700 rounded-full">On-Call</span>
                                </div>
                                <div class="flex items-center gap-4 mt-2 text-xs text-slate-600">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                        </svg>
                                        Skill: <span class="font-semibold" x-text="rec.skill_match_score + '%'"></span>
                                    </span>
                                    <span x-show="rec.distance_km !== null" class="flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span x-text="rec.distance_km + ' km'"></span>
                                        <span x-show="rec.estimated_duration_minutes" x-text="'(' + rec.estimated_duration_minutes + ' min)'"></span>
                                    </span>
                                    <span x-show="rec.distance_km === null" class="text-slate-400">Distance: N/A</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-slate-500 mb-1">Match Score</div>
                                <div class="text-lg font-bold text-blue-600" x-text="rec.combined_score + '%'"></div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        
        <!-- Loading State -->
        <div x-show="loadingRecommendations" 
             x-transition
             class="mb-6 text-center py-4">
            <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
            <p class="text-sm text-slate-500 mt-2">Loading recommendations...</p>
        </div>
        
        <!-- No Recommendations Message -->
        <div x-show="!loadingRecommendations && recommendations && recommendations.length === 0" 
             x-transition
             class="mb-6 text-center py-4 border border-slate-200 rounded-lg bg-slate-50">
            <svg class="w-8 h-8 text-slate-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-sm text-slate-500">No recommendations available</p>
            <p class="text-xs text-slate-400 mt-1">All technicians may be busy or unavailable</p>
        </div>
        
        <form method="POST" id="assign-form" action="" data-base-url="/admin/triage">
            @csrf
            <input type="hidden" name="ticket_id" id="ticket-id-input" value="">
            <div class="mb-6">
                <label class="block text-sm font-medium text-slate-700 mb-2">Select Technician</label>
                <select name="technician_id" id="technician-select" required 
                        x-model="selectedTechnician"
                        @change="selectedTechnician = $event.target.value"
                        class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    <option value="">Choose a technician...</option>
                    @foreach($technicians as $tech)
                        @if($tech['available'])
                            <option value="{{ $tech['id'] }}">
                                {{ $tech['name'] }} 
                                @if($tech['is_on_call']) (On-Call) @endif
                                (Available)
                            </option>
                        @else
                            <option value="{{ $tech['id'] }}" disabled>
                                {{ $tech['name'] }} ({{ $tech['active_jobs'] }} active jobs)
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" 
                        @click.stop="assignModalOpen = false; window.closeAssignModal && window.closeAssignModal();"
                        onclick="event.preventDefault(); event.stopPropagation(); window.closeAssignModal && window.closeAssignModal(); return false;"
                        class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors font-medium cursor-pointer focus:outline-none focus:ring-2 focus:ring-slate-500">
                    Cancel
                </button>
                <button type="button" id="assign-submit-btn"
                        @click.stop="window.submitAssignFormAlpine($event)"
                        onclick="console.log('=== onclick fallback fired ==='); if(typeof window.handleAssignSubmit === 'function') { var e = event || window.event; if(e) { e.preventDefault(); e.stopPropagation(); e.stopImmediatePropagation(); } window.handleAssignSubmit(e); } return false;"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium shadow-sm hover:shadow-md">
                    Assign Now
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection

@push('scripts')
<script>
    // Real-time countdown timer
    function updateCountdowns() {
        document.querySelectorAll('[data-deadline]').forEach(function(element) {
            const deadline = new Date(element.getAttribute('data-deadline'));
            const now = new Date();
            const diff = Math.max(0, Math.floor((deadline - now) / 1000)); // seconds remaining
            
            const countdownText = element.querySelector('.countdown-text');
            const countdownProgress = element.querySelector('.countdown-progress');
            
            if (diff > 0) {
                // Format as MM:SS
                const minutes = Math.floor(diff / 60);
                const seconds = diff % 60;
                const formatted = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
                
                countdownText.textContent = formatted;
                
                // Update color if less than 60 seconds
                if (diff < 60) {
                    countdownText.classList.remove('text-slate-600');
                    countdownText.classList.add('text-pulse-orange');
                    element.closest('.ticket-enter')?.classList.add('timer-urgent');
                } else {
                    countdownText.classList.remove('text-pulse-orange');
                    countdownText.classList.add('text-slate-600');
                }
                
                // Update progress circle (assuming 5 minutes = 300 seconds total)
                const totalSeconds = 300; // 5 minutes
                const percentage = Math.min(100, (diff / totalSeconds) * 100);
                const dashOffset = 100 - percentage;
                countdownProgress.style.strokeDashoffset = dashOffset;
            } else {
                countdownText.textContent = '00:00';
                countdownText.classList.remove('text-slate-600');
                countdownText.classList.add('text-pulse-orange');
                if (countdownProgress) {
                    countdownProgress.style.strokeDashoffset = 0;
                }
            }
        });
    }

    // Update countdowns every second
    setInterval(updateCountdowns, 1000);
    updateCountdowns(); // Initial update

    // Auto-refresh every 30 seconds (reduced frequency since countdown updates in real-time)
    setInterval(() => {
        location.reload();
    }, 60000); // Changed to 60 seconds since we have real-time countdown

    window.submitAssignFormAlpine = function(event) {
        console.log('=== Alpine submitAssignForm called ===', event);
        if (event) {
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();
        }
        console.log('Calling handleAssignSubmit, available:', typeof window.handleAssignSubmit);
        if (typeof window.handleAssignSubmit === 'function') {
            try {
                window.handleAssignSubmit(event);
            } catch (error) {
                console.error('Error calling handleAssignSubmit:', error);
                alert('Error submitting form: ' + error.message);
            }
        } else {
            console.error('handleAssignSubmit not available');
            alert('Form handler not loaded. Please refresh.');
        }
    };

    // Track which ticket is currently loading recommendations to prevent duplicates
    let loadingRecommendationsForTicket = null;
    
    // Function to load recommendations for a ticket
    function loadRecommendations(ticketId, alpineComponent) {
        if (!ticketId) {
            console.warn('No ticket ID provided to loadRecommendations');
            return;
        }
        
        // Prevent duplicate loads
        if (loadingRecommendationsForTicket === ticketId) {
            console.log('Recommendations already loading for ticket:', ticketId);
            return;
        }
        
        if (!alpineComponent) {
            const element = document.querySelector('[x-data]');
            if (element && typeof Alpine !== 'undefined' && element.__x) {
                alpineComponent = element.__x;
            } else {
                // Alpine not ready - don't retry here, let the caller handle it
                console.warn('Alpine component not ready, skipping recommendations load');
                return;
            }
        }
        
        if (!alpineComponent || !alpineComponent.$data) {
            console.warn('Alpine component or $data not available, skipping recommendations load');
            return;
        }
        
        loadingRecommendationsForTicket = ticketId;
        console.log('Loading recommendations for ticket:', ticketId);
        
        // Initialize recommendations array if it doesn't exist
        if (!alpineComponent.$data.recommendations) {
            alpineComponent.$data.recommendations = [];
        }
        alpineComponent.$data.loadingRecommendations = true;
        
        fetch(`/admin/triage/${ticketId}/recommendations`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.error || `HTTP error! status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Recommendations data received:', data);
            if (alpineComponent && alpineComponent.$data) {
                alpineComponent.$data.recommendations = data.recommendations || [];
                alpineComponent.$data.loadingRecommendations = false;
            }
            loadingRecommendationsForTicket = null;
            
            if (data.recommendations && data.recommendations.length === 0) {
                console.warn('No recommendations found for ticket:', ticketId);
            }
        })
        .catch(error => {
            console.error('Error loading recommendations:', error);
            if (alpineComponent && alpineComponent.$data) {
                alpineComponent.$data.loadingRecommendations = false;
                alpineComponent.$data.recommendations = [];
            }
            loadingRecommendationsForTicket = null;
            
            // Show user-friendly error message
            if (typeof alert !== 'undefined') {
                alert('Failed to load technician recommendations. Please try again.');
            }
        });
    }

    // Function to update form when ticket is selected
    window.lastUpdatedTicketId = null;
    let isUpdatingForm = false;
    
    window.updateAssignForm = function(ticketId) {
        // Prevent concurrent updates
        if (isUpdatingForm) {
            return;
        }
        
        // Don't update if it's the same ticket
        if (window.lastUpdatedTicketId === ticketId) {
            return;
        }
        
        if (!ticketId) {
            console.warn('No ticket ID provided to updateAssignForm');
            return;
        }
        
        isUpdatingForm = true;
        window.lastUpdatedTicketId = ticketId;
        
        console.log('Updating form for ticket:', ticketId);
        const form = document.getElementById('assign-form');
        const ticketInput = document.getElementById('ticket-id-input');
        if (form && ticketInput) {
            form.action = `/admin/triage/${ticketId}/assign`;
            ticketInput.value = ticketId;
            console.log('Form updated:', form.action, ticketInput.value);
        } else {
            console.error('Form elements not found');
        }
        
        // Load recommendations - wait for Alpine to be ready
        setTimeout(() => {
            const element = document.querySelector('[x-data]');
            if (element && typeof Alpine !== 'undefined' && element.__x) {
                loadRecommendations(ticketId, element.__x);
            } else {
                // Retry once after a delay
                setTimeout(() => {
                    const retryElement = document.querySelector('[x-data]');
                    if (retryElement && retryElement.__x) {
                        loadRecommendations(ticketId, retryElement.__x);
                    }
                }, 500);
            }
            isUpdatingForm = false;
        }, 200);
    };

    // Handle form submission directly - define early
    window.handleAssignSubmit = function(event) {
        console.log('=== handleAssignSubmit called ===');
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        const form = document.getElementById('assign-form');
        if (!form) {
            console.error('Form not found');
            alert('Form not found. Please refresh the page.');
            return;
        }
        
        const formData = new FormData(form);
        const ticketId = form.querySelector('#ticket-id-input')?.value || 
                        form.querySelector('input[name="ticket_id"]')?.value ||
                        window.currentTicketId;
        const technicianId = form.querySelector('select[name="technician_id"]')?.value;
        const csrfToken = form.querySelector('input[name="_token"]')?.value ||
                         document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        console.log('Form data:', { ticketId, technicianId, hasCsrf: !!csrfToken });
        
        if (!ticketId) {
            alert('Error: Ticket ID not found. Please refresh and try again.');
            console.error('Ticket ID missing');
            return;
        }
        
        if (!technicianId) {
            alert('Please select a technician');
            return;
        }
        
        if (!csrfToken) {
            alert('CSRF token not found. Please refresh the page.');
            console.error('CSRF token missing');
            return;
        }
        
        const submitBtn = form.querySelector('#assign-submit-btn');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Assigning...';
        
        fetch(`/admin/triage/${ticketId}/assign`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                technician_id: parseInt(technicianId),
                _token: csrfToken
            })
        })
        .then(response => {
            console.log('Response received:', response.status, response.statusText);
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                if (response.ok) {
                    return response.json().catch(() => ({ message: 'Success' }));
                }
                return response.json().then(err => {
                    console.error('Error response:', err);
                    return Promise.reject(err);
                });
            } else {
                if (response.ok || response.redirected) {
                    return { message: 'Success', redirect: true };
                }
                throw new Error('Request failed with status: ' + response.status);
            }
        })
        .then(data => {
            console.log('Success:', data);
            alert(data.message || 'Ticket assigned successfully!');
            location.reload();
        })
        .catch(error => {
            console.error('Fetch error:', error);
            const errorMsg = error.message || error.error || 'Failed to assign ticket. Please try again.';
            alert(errorMsg);
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        });
    };

    // Handle form submission with fetch for better UX
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, setting up form handler');
        console.log('Alpine.js loaded:', typeof Alpine !== 'undefined');
        
        // Update form when modal opens and attach submit handler
        // Only observe the modal element, not the entire container
        let observerActive = false;
        let lastObserverCheck = 0;
        let lastModalState = false;
        
        const observer = new MutationObserver(function(mutations) {
            // Throttle observer to prevent infinite loops (max once per 1000ms)
            const now = Date.now();
            if (observerActive || (now - lastObserverCheck) < 1000) {
                return;
            }
            
            observerActive = true;
            lastObserverCheck = now;
            
            try {
                const modal = document.querySelector('[x-show="assignModalOpen"]');
                if (!modal) {
                    observerActive = false;
                    return;
                }
                
                // Check if modal is actually visible using computed styles
                const computedStyle = window.getComputedStyle(modal);
                const isVisible = computedStyle.display !== 'none' && 
                                 computedStyle.visibility !== 'hidden' &&
                                 computedStyle.opacity !== '0';
                
                // Only process if modal state changed (opened)
                if (isVisible && !lastModalState) {
                    lastModalState = true;
                    
                    // Get ticket ID from Alpine or window
                    let ticketId = window.currentTicketId;
                    if (!ticketId) {
                        const alpineElement = document.querySelector('[x-data]');
                        if (alpineElement && alpineElement.__x) {
                            ticketId = alpineElement.__x.$data?.selectedTicket;
                        }
                    }
                    
                    // Only update if ticket ID changed and we have a valid ticket
                    if (ticketId && ticketId !== window.lastUpdatedTicketId) {
                        // Check if Alpine is ready before calling updateAssignForm
                        const alpineElement = document.querySelector('[x-data]');
                        if (alpineElement && alpineElement.__x) {
                            updateAssignForm(ticketId);
                        } else {
                            // Alpine not ready - skip, openAssignModal will handle it
                            console.log('Skipping updateAssignForm - Alpine not ready');
                        }
                    }
                } else if (!isVisible) {
                    lastModalState = false;
                }
                    
                    // Attach submit button click handler
                    const submitBtn = document.getElementById('assign-submit-btn');
                    if (submitBtn && !submitBtn.hasAttribute('data-handler-attached')) {
                        submitBtn.setAttribute('data-handler-attached', 'true');
                        submitBtn.addEventListener('click', function(e) {
                            console.log('Submit button clicked via event listener');
                            e.preventDefault();
                            e.stopPropagation();
                            if (typeof window.handleAssignSubmit === 'function') {
                                window.handleAssignSubmit(e);
                            } else {
                                console.error('handleAssignSubmit function not found');
                                alert('Error: Form handler not loaded. Please refresh the page.');
                            }
                        });
                        console.log('Submit button handler attached');
                    }
                } else if (!isVisible) {
                    lastModalState = false;
                }
            } catch (error) {
                console.error('Error in MutationObserver:', error);
            } finally {
                observerActive = false;
            }
        });
        
        const container = document.querySelector('[x-data]');
        if (container) {
            observer.observe(container, { attributes: true, attributeFilter: ['style', 'class'], childList: true, subtree: true });
        }
        
        // Also check immediately in case modal is already open
        setTimeout(function() {
            const submitBtn = document.getElementById('assign-submit-btn');
            if (submitBtn) {
                if (!submitBtn.hasAttribute('data-handler-attached')) {
                    submitBtn.setAttribute('data-handler-attached', 'true');
                    submitBtn.addEventListener('click', function(e) {
                        console.log('Submit button clicked via event listener (immediate)');
                        e.preventDefault();
                        e.stopPropagation();
                        if (typeof window.handleAssignSubmit === 'function') {
                            window.handleAssignSubmit(e);
                        } else {
                            console.error('handleAssignSubmit function not found');
                            alert('Error: Form handler not loaded. Please refresh the page.');
                        }
                    }, true); // Use capture phase
                    console.log('Submit button handler attached (immediate)');
                }
                // Test if button is clickable
                console.log('Submit button found:', {
                    id: submitBtn.id,
                    type: submitBtn.type,
                    disabled: submitBtn.disabled,
                    visible: submitBtn.offsetParent !== null
                });
            } else {
                console.log('Submit button not found in DOM');
            }
        }, 1000);
        
        // Use event delegation for button clicks (more reliable) - capture phase
        document.addEventListener('click', function(e) {
            const target = e.target;
            // Check if clicked element is the button or inside it
            const submitBtn = target.closest('#assign-submit-btn') || 
                             (target.id === 'assign-submit-btn' ? target : null) ||
                             (target.closest('button')?.id === 'assign-submit-btn' ? target.closest('button') : null);
            
            if (submitBtn && submitBtn.id === 'assign-submit-btn') {
                console.log('=== Assign submit button clicked via delegation (CAPTURE) ===', {
                    target: target.tagName,
                    targetId: target.id,
                    buttonId: submitBtn.id,
                    buttonType: submitBtn.type,
                    buttonDisabled: submitBtn.disabled,
                    buttonVisible: submitBtn.offsetParent !== null
                });
                
                // Don't prevent default here - let Alpine handle it first, then our handler
                // Only prevent if Alpine didn't handle it
                setTimeout(() => {
                    if (!e.defaultPrevented) {
                        console.log('Event not prevented by Alpine, handling manually');
                        e.preventDefault();
                        e.stopPropagation();
                        if (typeof window.handleAssignSubmit === 'function') {
                            window.handleAssignSubmit(e);
                        }
                    }
                }, 0);
            }
        }, true); // Use capture phase to catch early
        
        // Use event delegation for form submission (backup)
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form.id === 'assign-form') {
                console.log('Assign form submitted');
                e.preventDefault();
                
                const formData = new FormData(form);
                const ticketId = form.querySelector('#ticket-id-input')?.value || 
                                form.querySelector('input[name="ticket_id"]')?.value;
                const technicianId = form.querySelector('select[name="technician_id"]')?.value;
                const csrfToken = form.querySelector('input[name="_token"]')?.value ||
                                 document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                console.log('Form data:', { ticketId, technicianId, hasCsrf: !!csrfToken });
                
                if (!ticketId) {
                    alert('Error: Ticket ID not found. Please refresh and try again.');
                    console.error('Ticket ID missing');
                    return;
                }
                
                if (!technicianId) {
                    alert('Please select a technician');
                    return;
                }
                
                const submitBtn = form.querySelector('#assign-submit-btn');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Assigning...';
                
                fetch(`/admin/triage/${ticketId}/assign`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        technician_id: parseInt(technicianId),
                        _token: csrfToken
                    })
                })
                .then(response => {
                    console.log('Response received:', response.status, response.statusText);
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        if (response.ok) {
                            return response.json().catch(() => ({ message: 'Success' }));
                        }
                        return response.json().then(err => {
                            console.error('Error response:', err);
                            return Promise.reject(err);
                        });
                    } else {
                        if (response.ok || response.redirected) {
                            return { message: 'Success', redirect: true };
                        }
                        throw new Error('Request failed with status: ' + response.status);
                    }
                })
                .then(data => {
                    console.log('Success:', data);
                    alert(data.message || 'Ticket assigned successfully!');
                    location.reload();
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    const errorMsg = error.message || error.error || 'Failed to assign ticket. Please try again.';
                    alert(errorMsg);
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                });
            }
        });
        
        // Debug: Log button clicks
        document.addEventListener('click', function(e) {
            if (e.target.closest('button[data-ticket-id]')) {
                const btn = e.target.closest('button[data-ticket-id]');
                console.log('Assign button clicked, ticket ID:', btn.getAttribute('data-ticket-id'));
            }
        });
    });
</script>
@endpush
