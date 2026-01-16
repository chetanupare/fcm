@extends('layouts.app')

@section('title', 'Payment Reconciliation')
@section('page-title', 'Payment Reconciliation')

@section('content')
<div class="space-y-6" x-data="{ selectedDate: '{{ now()->format('Y-m-d') }}', report: null, loading: false }">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-2xl font-bold text-slate-800">Payment Reconciliation</h3>
            <p class="text-sm text-slate-500 mt-1">Daily reconciliation reports and payment matching</p>
        </div>
        <div class="flex items-center gap-3">
            <input type="date" 
                   x-model="selectedDate"
                   @change="loadReport()"
                   class="border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <button @click="loadReport()" 
                    :disabled="loading"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium disabled:opacity-50">
                <span x-show="!loading">Load Report</span>
                <span x-show="loading" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Loading...
                </span>
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div x-show="report" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-sm font-semibold text-slate-600">Cash Collected</h4>
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <p class="text-3xl font-bold text-slate-800" x-text="report ? formatCurrency(report.cash.collected) : '0.00'"></p>
            <p class="text-sm text-slate-500 mt-1" x-text="report ? report.cash.count + ' transactions' : ''"></p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-sm font-semibold text-slate-600">Online Collected</h4>
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v4a3 3 0 003 3z"></path>
                </svg>
            </div>
            <p class="text-3xl font-bold text-slate-800" x-text="report ? formatCurrency(report.online.collected) : '0.00'"></p>
            <p class="text-sm text-slate-500 mt-1" x-text="report ? report.online.count + ' transactions' : ''"></p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-sm font-semibold text-slate-600">Expected Total</h4>
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            <p class="text-3xl font-bold text-slate-800" x-text="report ? formatCurrency(report.summary.expected_total) : '0.00'"></p>
            <p class="text-sm text-slate-500 mt-1">From completed jobs</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-sm font-semibold text-slate-600">Difference</h4>
                <svg class="w-8 h-8" :class="report && report.summary.difference >= 0 ? 'text-green-600' : 'text-red-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
            </div>
            <p class="text-3xl font-bold" 
               :class="report && report.summary.difference >= 0 ? 'text-green-600' : 'text-red-600'"
               x-text="report ? formatCurrency(report.summary.difference) : '0.00'"></p>
            <p class="text-sm text-slate-500 mt-1" x-text="report && report.summary.difference < 0 ? 'Investigation needed' : 'Balanced'"></p>
        </div>
    </div>

    <!-- Outstanding Receivables -->
    <div x-show="report && report.summary.outstanding_amount > 0" class="bg-white rounded-2xl border border-slate-100 shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 bg-yellow-50">
            <h4 class="text-lg font-bold text-slate-800">Outstanding Receivables</h4>
            <p class="text-sm text-slate-600 mt-1">
                <span x-text="report.summary.outstanding_count"></span> jobs with outstanding payments totaling 
                <span class="font-semibold" x-text="formatCurrency(report.summary.outstanding_amount)"></span>
            </p>
        </div>
    </div>

    <!-- Exceptions -->
    <div x-show="report && report.exceptions.unmatched_payments > 0" class="bg-white rounded-2xl border border-red-200 shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-red-200 bg-red-50">
            <h4 class="text-lg font-bold text-red-800">⚠️ Exceptions Requiring Attention</h4>
            <p class="text-sm text-red-600 mt-1">
                <span x-text="report.exceptions.unmatched_payments"></span> unmatched payments found
            </p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Payment ID</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Amount</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Method</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Transaction ID</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <template x-for="payment in (report ? report.exceptions.unmatched_payments_list : [])" :key="payment.id">
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 text-sm font-semibold text-slate-800" x-text="'#' + payment.id"></td>
                            <td class="px-6 py-4 text-sm text-slate-800" x-text="formatCurrency(payment.amount)"></td>
                            <td class="px-6 py-4 text-sm text-slate-600" x-text="payment.method"></td>
                            <td class="px-6 py-4 text-sm text-slate-600" x-text="payment.transaction_id || '-'"></td>
                            <td class="px-6 py-4 text-sm text-slate-600" x-text="formatDate(payment.created_at)"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payment Details Tables -->
    <div x-show="report" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Cash Payments -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-green-50">
                <h4 class="text-lg font-bold text-slate-800">Cash Payments</h4>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Payment ID</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <template x-for="payment in (report ? report.cash.payments : [])" :key="payment.id">
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-sm font-semibold text-slate-800" x-text="'#' + payment.id"></td>
                                <td class="px-4 py-3 text-sm text-slate-600" x-text="payment.customer"></td>
                                <td class="px-4 py-3 text-sm font-semibold text-slate-800" x-text="formatCurrency(payment.amount)"></td>
                                <td class="px-4 py-3 text-sm text-slate-600" x-text="formatTime(payment.created_at)"></td>
                            </tr>
                        </template>
                        <tr x-show="report && report.cash.payments.length === 0">
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500 text-sm">No cash payments</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Online Payments -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-blue-50">
                <h4 class="text-lg font-bold text-slate-800">Online Payments</h4>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Payment ID</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Method</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Transaction ID</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <template x-for="payment in (report ? report.online.payments : [])" :key="payment.id">
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-sm font-semibold text-slate-800" x-text="'#' + payment.id"></td>
                                <td class="px-4 py-3 text-sm text-slate-600" x-text="payment.method"></td>
                                <td class="px-4 py-3 text-sm font-semibold text-slate-800" x-text="formatCurrency(payment.amount)"></td>
                                <td class="px-4 py-3 text-sm text-slate-600 font-mono text-xs" x-text="payment.transaction_id || '-'"></td>
                            </tr>
                        </template>
                        <tr x-show="report && report.online.payments.length === 0">
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500 text-sm">No online payments</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function loadReport() {
        const alpineComponent = document.querySelector('[x-data]')?.__x;
        if (!alpineComponent) return;
        
        alpineComponent.$data.loading = true;
        
        const date = alpineComponent.$data.selectedDate;
        
        fetch(`/api/admin/reconciliation/daily?date=${date}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            alpineComponent.$data.report = data;
            alpineComponent.$data.loading = false;
        })
        .catch(error => {
            console.error('Error loading report:', error);
            alpineComponent.$data.loading = false;
            alert('Error loading reconciliation report. Please try again.');
        });
    }
    
    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount || 0);
    }
    
    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    }
    
    function formatTime(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
    }
    
    // Make functions available globally for Alpine.js
    window.formatCurrency = formatCurrency;
    window.formatDate = formatDate;
    window.formatTime = formatTime;
    window.loadReport = loadReport;
    
    // Load today's report on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadReport();
    });
</script>
@endpush
@endsection
