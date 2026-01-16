@extends('layouts.app')

@section('title', 'Revenue Report')
@section('page-title', 'Revenue Report')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-2xl font-bold text-slate-800">Revenue Report</h3>
            <p class="text-sm text-slate-500 mt-1">Financial performance analysis</p>
        </div>
        <a href="{{ route('admin.reports.index') }}" 
           class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl hover:bg-slate-200 transition-all font-medium">
            ← Back to Reports
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6">
        <form method="GET" action="{{ route('admin.reports.revenue') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="start_date" class="block text-sm font-semibold text-slate-700 mb-2">Start Date</label>
                <input type="date" name="start_date" id="start_date" 
                       value="{{ $startDate->format('Y-m-d') }}"
                       class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-semibold text-slate-700 mb-2">End Date</label>
                <input type="date" name="end_date" id="end_date" 
                       value="{{ $endDate->format('Y-m-d') }}"
                       class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
            </div>
            <div>
                <label for="group_by" class="block text-sm font-semibold text-slate-700 mb-2">Group By</label>
                <select name="group_by" id="group_by" 
                        class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    <option value="day" {{ $groupBy === 'day' ? 'selected' : '' }}>Day</option>
                    <option value="week" {{ $groupBy === 'week' ? 'selected' : '' }}>Week</option>
                    <option value="month" {{ $groupBy === 'month' ? 'selected' : '' }}>Month</option>
                    <option value="year" {{ $groupBy === 'year' ? 'selected' : '' }}>Year</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" 
                        class="w-full px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all font-medium shadow-lg hover:shadow-xl">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-xl p-6 text-white">
            <p class="text-sm font-medium text-green-100 mb-2">Total Revenue</p>
            <p class="text-3xl font-bold">@currency($summary['total_revenue'])</p>
        </div>
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-xl p-6 text-white">
            <p class="text-sm font-medium text-blue-100 mb-2">Total Tips</p>
            <p class="text-3xl font-bold">@currency($summary['total_tips'])</p>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-xl p-6 text-white">
            <p class="text-sm font-medium text-purple-100 mb-2">Transactions</p>
            <p class="text-3xl font-bold">{{ number_format($summary['total_transactions']) }}</p>
        </div>
        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-2xl shadow-xl p-6 text-white">
            <p class="text-sm font-medium text-yellow-100 mb-2">Avg Transaction</p>
            <p class="text-3xl font-bold">@currency($summary['average_transaction'] ?? 0)</p>
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6">
        <h4 class="text-lg font-bold text-slate-800 mb-4">Revenue Trend</h4>
        <canvas id="revenueChart" height="100"></canvas>
    </div>

    <!-- Revenue Table -->
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6">
        <h4 class="text-lg font-bold text-slate-800 mb-4">Revenue Details</h4>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Period</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Revenue</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Tips</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Transactions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($revenue as $row)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 text-sm font-medium text-slate-800">{{ $row->period }}</td>
                        <td class="px-4 py-3 text-sm text-right font-mono text-slate-700">@currency($row->total)</td>
                        <td class="px-4 py-3 text-sm text-right font-mono text-slate-600">@currency($row->tips ?? 0)</td>
                        <td class="px-4 py-3 text-sm text-right text-slate-600">{{ $row->transactions }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-slate-500">No revenue data found for the selected period</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const ctx = document.getElementById('revenueChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($revenue->pluck('period')) !!},
                datasets: [{
                    label: 'Revenue',
                    data: {!! json_encode($revenue->pluck('total')) !!},
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Tips',
                    data: {!! json_encode($revenue->pluck('tips')) !!},
                    borderColor: 'rgb(249, 115, 22)',
                    backgroundColor: 'rgba(249, 115, 22, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': $' + context.parsed.y.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toFixed(2);
                            }
                        }
                    }
                }
            }
        });
    }
</script>
@endpush
@endsection
