@extends('layouts.app')

@section('title', 'Triage Queue')
@section('page-title', 'Triage Queue')

@section('content')
<!-- Toast Container -->
<div id="toast-container" class="fixed top-4 right-4 z-[9999] space-y-2"></div>

<script>
    // CRITICAL: Define these functions BEFORE Alpine.js processes the page
    // This script runs immediately when the page loads, before Alpine initializes
    
    // Modern Toast Notification System
    function showToast(message, type = 'info', duration = 3000) {
        const container = document.getElementById('toast-container');
        if (!container) {
            console.warn('Toast container not found, falling back to alert');
            alert(message);
            return;
        }
        
        const toastId = 'toast-' + Date.now();
        const typeColors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            warning: 'bg-yellow-500',
            info: 'bg-blue-500'
        };
        const typeIcons = {
            success: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>',
            error: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>',
            warning: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
            info: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
        };
        
        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg text-white ${typeColors[type] || typeColors.info} transform transition-all duration-300 ease-in-out min-w-[300px] max-w-md`;
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        
        toast.innerHTML = `
            <div class="flex-shrink-0">
                ${typeIcons[type] || typeIcons.info}
            </div>
            <div class="flex-1 text-sm font-medium">${message}</div>
            <button onclick="closeToast('${toastId}')" class="flex-shrink-0 text-white hover:text-gray-200 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;
        
        container.appendChild(toast);
        
        // Animate in
        setTimeout(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(0)';
        }, 10);
        
        // Auto-remove after duration
        if (duration > 0) {
            setTimeout(() => {
                closeToast(toastId);
            }, duration);
        }
    }
    
    function closeToast(toastId) {
        const toast = document.getElementById(toastId);
        if (toast) {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => {
                toast.remove();
            }, 300);
        }
    }
    
    window.showToast = showToast;
    window.closeToast = closeToast;
    
    // Track which ticket is currently loading recommendations to prevent duplicates
    let loadingRecommendationsForTicket = null;
    
    // Function to load recommendations for a ticket - Define FIRST so it's available
    window.loadRecommendations = function loadRecommendations(ticketId, alpineComponent) {
        console.log('=== loadRecommendations FUNCTION CALLED ===');
        console.log('Ticket ID:', ticketId);
        console.log('Alpine Component provided:', alpineComponent ? 'Yes' : 'No');
        
        if (!ticketId) {
            console.error('ERROR: No ticket ID provided to loadRecommendations');
            return;
        }
        
        // Prevent duplicate loads
        if (loadingRecommendationsForTicket === ticketId) {
            console.log('Recommendations already loading for ticket:', ticketId);
            return;
        }
        
        // Get Alpine component if not provided
        if (!alpineComponent) {
            console.log('Alpine component not provided, trying to find it...');
            const element = document.querySelector('[x-data]');
            if (element && typeof Alpine !== 'undefined' && element.__x) {
                alpineComponent = element.__x;
                console.log('Found Alpine component');
            } else {
                console.error('ERROR: Alpine component not ready, cannot load recommendations');
                // Retry after a delay
                setTimeout(() => {
                    const retryElement = document.querySelector('[x-data]');
                    if (retryElement && retryElement.__x) {
                        console.log('Retrying loadRecommendations after delay...');
                        window.loadRecommendations(ticketId, retryElement.__x);
                    }
                }, 200);
                return;
            }
        }
        
        if (!alpineComponent || !alpineComponent.$data) {
            console.error('ERROR: Alpine component or $data not available');
            return;
        }
        
        loadingRecommendationsForTicket = ticketId;
        console.log('=== STARTING RECOMMENDATIONS FETCH ===');
        console.log('Ticket ID:', ticketId);
        console.log('Alpine Component:', alpineComponent ? 'Found' : 'Missing');
        
        // Initialize recommendations array if it doesn't exist
        if (!alpineComponent.$data.recommendations) {
            alpineComponent.$data.recommendations = [];
        }
        alpineComponent.$data.loadingRecommendations = true;
        console.log('Set loadingRecommendations to true');
        
        const url = `/admin/triage/${ticketId}/recommendations`;
        console.log('=== FETCHING RECOMMENDATIONS ===');
        console.log('URL:', url);
        console.log('Full URL will be:', window.location.origin + url);
        console.log('Ticket ID:', ticketId);
        console.log('Alpine Component:', alpineComponent ? 'Found' : 'Missing');
        
        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('=== RESPONSE RECEIVED ===');
            console.log('Status:', response.status);
            console.log('Status Text:', response.statusText);
            console.log('OK:', response.ok);
            
            if (!response.ok) {
                return response.json().then(data => {
                    console.error('=== ERROR RESPONSE ===');
                    console.error('Error Data:', data);
                    throw new Error(data.error || data.message || `HTTP error! status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('=== RECOMMENDATIONS DATA ===');
            console.log('Full Response:', JSON.stringify(data, null, 2));
            console.log('Recommendations Count:', data.recommendations ? data.recommendations.length : 0);
            console.log('Debug Info:', data.debug);
            
            if (alpineComponent && alpineComponent.$data) {
                console.log('Setting recommendations in Alpine...');
                alpineComponent.$data.recommendations = data.recommendations || [];
                alpineComponent.$data.loadingRecommendations = false;
                console.log('Alpine recommendations set:', alpineComponent.$data.recommendations.length);
            } else {
                console.error('Alpine component or $data not available!');
            }
            loadingRecommendationsForTicket = null;
            
            if (data.recommendations && data.recommendations.length === 0) {
                console.warn('=== NO RECOMMENDATIONS FOUND ===');
                console.warn('Ticket ID:', ticketId);
                console.warn('Debug Info:', data.debug);
            } else if (data.recommendations && data.recommendations.length > 0) {
                console.log('=== RECOMMENDATIONS FOUND ===');
                data.recommendations.forEach((rec, index) => {
                    console.log(`Recommendation ${index + 1}:`, {
                        id: rec.id,
                        name: rec.name,
                        skill_score: rec.skill_match_score,
                        combined_score: rec.combined_score
                    });
                });
            }
        })
        .catch(error => {
            console.error('=== ERROR LOADING RECOMMENDATIONS ===');
            console.error('Error:', error);
            if (alpineComponent && alpineComponent.$data) {
                alpineComponent.$data.loadingRecommendations = false;
                alpineComponent.$data.recommendations = [];
            }
            loadingRecommendationsForTicket = null;
            
            if (typeof alert !== 'undefined') {
                alert('Failed to load technician recommendations. Please try again.');
            }
        });
    };
    
    window.openAssignModal = function(ticketId) {
        console.log('=== openAssignModal called for ticket:', ticketId);
        
        window.currentTicketId = ticketId;
        
        // Wait for Alpine to be ready
        let retryCount = 0;
        const maxRetries = 5; // 5 * 50ms = 250ms max wait
        
        const checkAlpine = () => {
            retryCount++;
            console.log(`=== checkAlpine attempt ${retryCount}/${maxRetries} ===`);
            
            // Try multiple selectors to find Alpine component
            const element = document.querySelector('[x-data]') || 
                           document.querySelector('.space-y-6[x-data]') ||
                           document.querySelector('div[x-data]');
            console.log('Element found:', element ? 'Yes' : 'No');
            if (element) {
                console.log('Element tag:', element.tagName);
                console.log('Element classes:', element.className);
                console.log('Element has x-data:', element.hasAttribute('x-data'));
            }
            console.log('Element __x:', element && element.__x ? 'Yes' : 'No');
            console.log('Alpine defined:', typeof Alpine !== 'undefined' ? 'Yes' : 'No');
            if (typeof Alpine !== 'undefined') {
                console.log('Alpine version:', Alpine.version || 'unknown');
            }
            
            if (element && element.__x) {
                const alpineComponent = element.__x;
                console.log('Alpine component found! Setting modal state...');
                alpineComponent.$data.selectedTicket = ticketId;
                alpineComponent.$data.assignModalOpen = true;
                alpineComponent.$data.selectedTechnician = null;
                alpineComponent.$data.recommendations = [];
                alpineComponent.$data.loadingRecommendations = false;
                console.log('Modal state:', { assignModalOpen: alpineComponent.$data.assignModalOpen, selectedTicket: alpineComponent.$data.selectedTicket });
                
                // Load recommendations immediately
                console.log('=== LOADING RECOMMENDATIONS FROM openAssignModal ===');
                console.log('Ticket ID:', ticketId);
                console.log('Alpine component:', alpineComponent ? 'Found' : 'Missing');
                console.log('window.loadRecommendations type:', typeof window.loadRecommendations);
                console.log('window.loadRecommendations value:', window.loadRecommendations);
                
                // Force call - don't check, just call it
                if (ticketId) {
                    console.log('FORCING CALL to window.loadRecommendations...');
                    try {
                        if (typeof window.loadRecommendations === 'function') {
                            console.log('Calling window.loadRecommendations NOW...');
                            window.loadRecommendations(ticketId, alpineComponent);
                        } else {
                            console.error('ERROR: window.loadRecommendations is not a function!');
                            console.error('Type:', typeof window.loadRecommendations);
                            console.error('Value:', window.loadRecommendations);
                        }
                    } catch (error) {
                        console.error('ERROR calling loadRecommendations:', error);
                    }
                } else {
                    console.error('No ticket ID provided!');
                }
            } else {
                // Retry if Alpine not ready yet
                if (retryCount < maxRetries) {
                    console.log(`Alpine not ready, retrying in 50ms... (attempt ${retryCount}/${maxRetries})`);
                    setTimeout(checkAlpine, 50);
                } else {
                    console.error('ERROR: Alpine component not found after', maxRetries, 'attempts!');
                    console.error('Trying to load recommendations anyway...');
                    // Try to load recommendations even if Alpine isn't ready
                    if (typeof window.loadRecommendations === 'function') {
                        const element = document.querySelector('[x-data]');
                        if (element) {
                            // Wait a bit more and try again
                            setTimeout(() => {
                                if (element.__x) {
                                    window.loadRecommendations(ticketId, element.__x);
                                }
                            }, 200);
                        }
                    }
                }
            }
        };
        checkAlpine();
        
        // Force modal to show
        setTimeout(() => {
            const modal = document.querySelector('[x-show="assignModalOpen"]');
            if (modal) {
                console.log('Modal element found, removing inline style and x-cloak');
                modal.style.display = '';
                modal.removeAttribute('style');
                modal.removeAttribute('x-cloak');
            }
            
            // Update form after modal is visible
            if (typeof window.updateAssignForm === 'function') {
                window.updateAssignForm(ticketId);
            }
            
            // Also ensure recommendations are loaded after modal is visible (fallback)
            setTimeout(() => {
                console.log('=== FALLBACK: Attempting to load recommendations after modal visible ===');
                const element = document.querySelector('[x-data]') || 
                               document.querySelector('.space-y-6[x-data]') ||
                               document.querySelector('div[x-data]');
                console.log('Fallback - Element found:', element ? 'Yes' : 'No');
                console.log('Fallback - Element __x:', element && element.__x ? 'Yes' : 'No');
                
                if (element && element.__x) {
                    const alpineComponent = element.__x;
                    console.log('=== FALLBACK: Loading recommendations after modal visible ===');
                    if (typeof window.loadRecommendations === 'function') {
                        console.log('Fallback: Calling window.loadRecommendations...');
                        window.loadRecommendations(ticketId, alpineComponent);
                    } else {
                        console.error('window.loadRecommendations still not available in fallback!');
                    }
                } else {
                    console.error('Fallback: Alpine component still not found!');
                    console.error('Attempting direct API call as last resort...');
                    // Last resort: try to call the API directly
                    if (ticketId) {
                        const url = `/admin/triage/${ticketId}/recommendations`;
                        console.log('Making direct fetch call to:', url);
                        fetch(url, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            },
                            credentials: 'same-origin'
                        })
                        .then(response => response.json())
                        .then(data => {
                            console.log('Direct API call successful:', data);
                            console.log('Recommendations received:', data.recommendations?.length || 0);
                            
                            // Hide "no recommendations" message initially
                            const noRecommendationsMsg = document.getElementById('no-recommendations-message');
                            if (noRecommendationsMsg) {
                                noRecommendationsMsg.style.display = 'none';
                            }
                            
                            // Store recommendations globally for when Alpine initializes
                            window.pendingRecommendations = {
                                ticketId: ticketId,
                                recommendations: data.recommendations || [],
                                timestamp: Date.now()
                            };
                            
                            // If no recommendations, show the message
                            if (!data.recommendations || data.recommendations.length === 0) {
                                if (noRecommendationsMsg) {
                                    noRecommendationsMsg.style.display = 'block';
                                }
                            }
                            
                            // Try to force Alpine initialization first
                            const element = document.querySelector('[x-data]');
                            if (element && typeof Alpine !== 'undefined' && !element.__x) {
                                console.log('Attempting to force Alpine initialization...');
                                try {
                                    Alpine.initTree(element);
                                    console.log('Alpine.initTree called');
                                } catch (e) {
                                    console.warn('Alpine.initTree failed:', e);
                                }
                            }
                            
                            // Try to update Alpine if it becomes available - with multiple retries
                            let alpineRetryCount = 0;
                            const tryUpdateAlpine = () => {
                                alpineRetryCount++;
                                const el = document.querySelector('[x-data]');
                                if (el && el.__x) {
                                    console.log('Alpine component found! Updating recommendations...');
                                    el.__x.$data.recommendations = window.pendingRecommendations.recommendations;
                                    el.__x.$data.loadingRecommendations = false;
                                    console.log('Recommendations updated in Alpine:', el.__x.$data.recommendations.length);
                                    delete window.pendingRecommendations; // Clean up
                                } else if (alpineRetryCount < 40) { // Try for up to 2 seconds
                                    setTimeout(tryUpdateAlpine, 50);
                                } else {
                                    console.warn('Alpine never initialized, manually rendering recommendations...');
                                    // Manually render recommendations in the modal
                                    const recommendationsToRender = data.recommendations || [];
                                    renderRecommendationsManually(recommendationsToRender);
                                    
                                    // If no recommendations after manual render, show message
                                    if (recommendationsToRender.length === 0) {
                                        const noRecommendationsMsg = document.getElementById('no-recommendations-message');
                                        if (noRecommendationsMsg) {
                                            noRecommendationsMsg.style.display = 'block';
                                        }
                                    }
                                }
                            };
                            tryUpdateAlpine();
                        })
                        .catch(error => {
                            console.error('Direct API call failed:', error);
                        });
                    }
                }
            }, 300);
        }, 100);
    };
    
    // Define closeAssignModal function
    window.closeAssignModal = function() {
        console.log('=== closeAssignModal called ===');
        const element = document.querySelector('[x-data]');
        if (element && element.__x) {
            const alpineComponent = element.__x;
            alpineComponent.$data.assignModalOpen = false;
            alpineComponent.$data.selectedTicket = null;
            alpineComponent.$data.selectedTechnician = null;
            alpineComponent.$data.recommendations = [];
            alpineComponent.$data.loadingRecommendations = false;
        } else {
            // Fallback: directly hide the modal
            const modal = document.getElementById('assign-modal-overlay');
            if (modal) {
                modal.style.display = 'none';
            }
        }
    };
</script>

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
        
        <!-- No Recommendations Message - Hidden by default, shown only after loading completes with no results -->
        <div id="no-recommendations-message" 
             style="display: none;"
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

    // REMOVED DUPLICATE - Function is defined above in the content section script block

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
                console.log('updateAssignForm: Loading recommendations via Alpine component');
                if (typeof window.loadRecommendations === 'function') {
                    window.loadRecommendations(ticketId, element.__x);
                } else if (typeof loadRecommendations === 'function') {
                    loadRecommendations(ticketId, element.__x);
                } else {
                    console.error('loadRecommendations function not found in updateAssignForm');
                }
            } else {
                // Retry once after a delay
                setTimeout(() => {
                    const retryElement = document.querySelector('[x-data]');
                    if (retryElement && retryElement.__x) {
                        console.log('updateAssignForm: Retrying to load recommendations');
                        if (typeof window.loadRecommendations === 'function') {
                            window.loadRecommendations(ticketId, retryElement.__x);
                        } else if (typeof loadRecommendations === 'function') {
                            loadRecommendations(ticketId, retryElement.__x);
                        }
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
            const message = data.message || 'Ticket assigned successfully!';
            showToast(message, 'success', 2000);
            // Refresh page after toast is shown
            setTimeout(() => {
                location.reload();
            }, 2000);
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
    // Function to manually render recommendations when Alpine isn't available
    function renderRecommendationsManually(recommendations) {
        console.log('renderRecommendationsManually called with', recommendations.length, 'recommendations');
        
        // Hide loading and no recommendations messages
        const loadingContainer = document.querySelector('[x-show="loadingRecommendations"]');
        const noRecommendationsContainer = document.getElementById('no-recommendations-message');
        
        if (loadingContainer) {
            loadingContainer.style.display = 'none';
        }
        if (noRecommendationsContainer) {
            noRecommendationsContainer.style.display = 'none';
        }
        
        if (recommendations.length === 0) {
            // Show "no recommendations" message only after we've confirmed there are none
            if (noRecommendationsContainer) {
                noRecommendationsContainer.style.display = 'block';
            }
            return;
        }
        
        // Find the recommendations container
        const recommendationsSection = document.querySelector('[x-show="recommendations && recommendations.length > 0"]');
        const recommendationsList = recommendationsSection ? recommendationsSection.querySelector('.space-y-2') : null;
        
        if (!recommendationsList) {
            console.error('Recommendations list container not found');
            return;
        }
        
        // Show the recommendations container
        if (recommendationsSection) {
            recommendationsSection.style.display = 'block';
        }
        
        // Clear existing content (keep template if it exists)
        const template = recommendationsList.querySelector('template');
        const existingItems = recommendationsList.querySelectorAll(':not(template)');
        existingItems.forEach(item => item.remove());
        
        // Render each recommendation
        recommendations.forEach((rec, index) => {
            const isFirst = index === 0;
            const recDiv = document.createElement('div');
            recDiv.className = `border rounded-lg p-3 cursor-pointer transition-all ${isFirst ? 'border-yellow-400 bg-yellow-50 shadow-lg shadow-yellow-200/50' : 'border-slate-200 hover:border-blue-300 hover:bg-slate-50'}`;
            recDiv.onclick = function() {
                const select = document.getElementById('technician-select');
                if (select) {
                    select.value = rec.id;
                    select.dispatchEvent(new Event('change'));
                }
            };
            recDiv.innerHTML = `
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3">
                            ${isFirst ? `<svg class="w-5 h-5 text-yellow-500 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>` : ''}
                            <span class="text-xs font-bold text-slate-400">#${index + 1}</span>
                            <span class="font-semibold text-slate-800">${rec.name || 'Unknown'}</span>
                            ${isFirst ? '<span class="text-xs px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full font-semibold">Top Match</span>' : ''}
                            ${rec.is_on_call ? '<span class="text-xs px-2 py-0.5 bg-orange-100 text-orange-700 rounded-full">On-Call</span>' : ''}
                        </div>
                        <div class="mt-2 flex items-center gap-4 text-xs text-slate-600">
                            <span class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                Skill: <span class="font-semibold">${rec.skill_match_score.toFixed(0)}%</span>
                            </span>
                            ${rec.distance_km ? `<span class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span>${rec.distance_km.toFixed(1)} km</span>
                                ${rec.estimated_duration_minutes ? `<span>(${rec.estimated_duration_minutes} min)</span>` : ''}
                            </span>` : '<span class="text-slate-400">Distance: N/A</span>'}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-slate-500 mb-1">Match Score</div>
                        <div class="text-lg font-bold text-blue-600">${rec.combined_score.toFixed(0)}%</div>
                    </div>
                </div>
            `;
            recommendationsList.appendChild(recDiv);
        });
        
        console.log('Recommendations rendered manually:', recommendations.length);
    }
    
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
                    
                    // Attach submit button click handler (only when modal is visible)
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
                    const message = data.message || 'Ticket assigned successfully!';
                    showToast(message, 'success', 2000);
                    // Refresh page after toast is shown
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
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
