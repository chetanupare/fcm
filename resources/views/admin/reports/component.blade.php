@extends('layouts.app')

@section('title', 'Component Usage Report')
@section('page-title', 'Component Usage Report')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-2xl font-bold text-slate-800">Component Usage Report</h3>
            <p class="text-sm text-slate-500 mt-1">Analyze component consumption and profitability</p>
        </div>
        <a href="{{ route('admin.reports.index') }}" 
           class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl hover:bg-slate-200 transition-all font-medium">
            ← Back to Reports
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6">
        <form method="GET" action="{{ route('admin.reports.component') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
            <div class="flex items-end">
                <button type="submit" 
                        class="w-full px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all font-medium shadow-lg hover:shadow-xl">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Report Table -->
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6">
        <h4 class="text-lg font-bold text-slate-800 mb-4">Component Usage Statistics</h4>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Component</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">SKU</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Used</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Cost</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Jobs</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($usage as $item)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-800">{{ $item->name }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-sm font-mono text-slate-500">{{ $item->sku }}</p>
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-mono text-slate-700">{{ number_format($item->total_used) }}</td>
                        <td class="px-4 py-3 text-sm text-right font-mono font-semibold text-slate-800">@currency($item->total_cost ?? 0)</td>
                        <td class="px-4 py-3 text-sm text-right text-slate-600">{{ $item->jobs_count }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-500">No component usage data found for the selected period</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
