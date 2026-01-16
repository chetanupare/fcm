@extends('layouts.app')

@section('title', 'Technician Performance Report')
@section('page-title', 'Technician Performance Report')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-2xl font-bold text-slate-800">Technician Performance Report</h3>
            <p class="text-sm text-slate-500 mt-1">Track technician productivity and revenue</p>
        </div>
        <a href="{{ route('admin.reports.index') }}" 
           class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl hover:bg-slate-200 transition-all font-medium">
            ← Back to Reports
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6">
        <form method="GET" action="{{ route('admin.reports.technician') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                <label for="technician_id" class="block text-sm font-semibold text-slate-700 mb-2">Technician</label>
                <select name="technician_id" id="technician_id" 
                        class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    <option value="">All Technicians</option>
                    @foreach(\App\Models\Technician::with('user')->get() as $tech)
                        <option value="{{ $tech->id }}" {{ request('technician_id') == $tech->id ? 'selected' : '' }}>
                            {{ $tech->user->name }}
                        </option>
                    @endforeach
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

    <!-- Report Table -->
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6">
        <h4 class="text-lg font-bold text-slate-800 mb-4">Technician Performance</h4>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Technician</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Jobs</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Revenue</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Commission</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Commission Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($report as $item)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold">
                                    {{ substr($item['technician']->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-medium text-slate-800">{{ $item['technician']->user->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $item['technician']->user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-right text-slate-700">{{ $item['jobs_count'] }}</td>
                        <td class="px-4 py-3 text-sm text-right font-mono font-semibold text-slate-800">@currency($item['revenue'])</td>
                        <td class="px-4 py-3 text-sm text-right font-mono font-semibold text-green-600">@currency($item['commission'])</td>
                        <td class="px-4 py-3 text-sm text-right text-slate-600">{{ $item['technician']->commission_rate }}%</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-500">No technician data found for the selected period</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
