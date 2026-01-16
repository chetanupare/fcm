@extends('layouts.app')

@section('title', 'Triage Queue')
@section('page-title', 'Triage Queue')

@section('content')
<div class="space-y-6" x-data="{ 
    assignModalOpen: false, 
    selectedTicket: null,
    openAssignModal: function(ticketId) {
        console.log('=== openAssignModal called for ticket:', ticketId);
        this.selectedTicket = ticketId;
        this.assignModalOpen = true;
        window.currentTicketId = ticketId;
        console.log('Modal state:', { assignModalOpen: this.assignModalOpen, selectedTicket: this.selectedTicket });
        // Update form after a brief delay to ensure modal is visible
        setTimeout(() => {
            if (typeof updateAssignForm === 'function') {
                updateAssignForm(ticketId);
            }
        }, 100);
    },
    submitAssignForm: function(event) {
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
    }
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
                                    @click.stop="openAssignModal({{ $ticket['id'] }})"
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

<!-- Assign Modal - Glassmorphic -->
<div x-show="assignModalOpen" 
     x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click.away="assignModalOpen = false"
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     style="display: none !important;">
    <div @click.stop class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 transform transition-all ticket-enter">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-slate-800">Assign Technician</h3>
            <button @click="assignModalOpen = false" class="text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form method="POST" id="assign-form" action="" data-base-url="/admin/triage">
            @csrf
            <input type="hidden" name="ticket_id" id="ticket-id-input" value="">
            <div class="mb-6">
                <label class="block text-sm font-medium text-slate-700 mb-2">Select Technician</label>
                <select name="technician_id" id="technician-select" required 
                        class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    <option value="">Choose a technician...</option>
                    @foreach($technicians as $tech)
                        @if($tech['available'])
                            <option value="{{ $tech['id'] }}">{{ $tech['name'] }} (Available)</option>
                        @else
                            <option value="{{ $tech['id'] }}" disabled>{{ $tech['name'] }} ({{ $tech['active_jobs'] }} active jobs)</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" @click="assignModalOpen = false" 
                        class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors font-medium">
                    Cancel
                </button>
                <button type="button" id="assign-submit-btn"
                        @click.stop="submitAssignForm($event)"
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

    // Function to update form when ticket is selected
    window.updateAssignForm = function(ticketId) {
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
        const observer = new MutationObserver(function(mutations) {
            const modal = document.querySelector('[x-show="assignModalOpen"]');
            if (modal) {
                const isVisible = !modal.hasAttribute('style') || modal.style.display !== 'none';
                if (isVisible) {
                    const ticketId = window.currentTicketId || document.querySelector('[x-data]')?.__x?.$data?.selectedTicket;
                    if (ticketId) {
                        updateAssignForm(ticketId);
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
                }
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
