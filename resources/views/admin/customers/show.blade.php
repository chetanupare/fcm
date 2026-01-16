@extends('layouts.app')

@section('title', 'Customer Details')
@section('page-title', 'Customer Details - ' . $customer->name)

@section('content')
<div class="space-y-6">
    <!-- Customer Info Card -->
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-8">
        <div class="flex items-start justify-between mb-6">
            <div class="flex items-center gap-6">
                <div class="h-20 w-20 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold text-2xl shadow-lg">
                    {{ strtoupper(substr($customer->name, 0, 2)) }}
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-slate-800">{{ $customer->name }}</h3>
                    <p class="text-slate-500 mt-1">{{ $customer->email }}</p>
                    @if($customer->phone)
                        <p class="text-slate-500 text-sm mt-1">{{ $customer->phone }}</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.customers.edit', $customer) }}" 
                   class="px-4 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors font-medium">
                    Edit
                </a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4">
                <p class="text-xs text-blue-600 font-medium mb-1">Total Devices</p>
                <p class="text-3xl font-bold text-blue-700">{{ $stats['total_devices'] }}</p>
            </div>
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-4">
                <p class="text-xs text-purple-600 font-medium mb-1">Total Tickets</p>
                <p class="text-3xl font-bold text-purple-700">{{ $stats['total_tickets'] }}</p>
            </div>
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4">
                <p class="text-xs text-green-600 font-medium mb-1">Completed Jobs</p>
                <p class="text-3xl font-bold text-green-700">{{ $stats['completed_jobs'] }}</p>
            </div>
            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-4">
                <p class="text-xs text-yellow-600 font-medium mb-1">Total Spent</p>
                <p class="text-2xl font-bold text-yellow-700">@currency($stats['total_spent'])</p>
            </div>
        </div>
    </div>

    <!-- Devices -->
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white">
            <h3 class="text-lg font-bold text-slate-800">Devices ({{ $customer->devices->count() }})</h3>
        </div>
        <div class="p-6">
            @forelse($customer->devices as $device)
                <div class="group relative grid grid-cols-5 gap-4 items-center bg-slate-50 p-4 rounded-xl border border-slate-100 shadow-sm hover:shadow-md hover:border-blue-200 transition-all duration-200 mb-3">
                    <div class="col-span-2 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold text-slate-800 text-sm">{{ $device->brand }} {{ ucfirst($device->device_type) }}</p>
                            <p class="text-xs text-slate-500">{{ $device->model ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div>
                        <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs font-medium">
                            {{ ucfirst($device->device_type) }}
                        </span>
                    </div>
                    <div class="text-sm text-slate-600">
                        <p class="text-xs text-slate-500">Serial</p>
                        <p class="font-mono text-xs">{{ $device->serial_number ?? 'N/A' }}</p>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-slate-500">{{ $device->tickets->count() }} tickets</span>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500 text-center py-8">No devices registered</p>
            @endforelse
        </div>
    </div>

    <!-- Job History -->
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white">
            <h3 class="text-lg font-bold text-slate-800">Job History</h3>
        </div>
        <div class="p-6">
            @forelse($jobHistory as $job)
                <div class="group relative grid grid-cols-6 gap-4 items-center bg-slate-50 p-4 rounded-xl border border-slate-100 shadow-sm hover:shadow-md hover:border-blue-200 transition-all duration-200 mb-3 ticket-enter">
                    <div class="col-span-2 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-lg bg-purple-50 flex items-center justify-center text-purple-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold text-slate-800 text-sm">Job #{{ $job->id }}</p>
                            <p class="text-xs text-slate-500">{{ $job->ticket->device->brand }} - {{ $job->technician->user->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border shadow-sm
                            {{ $job->status === 'completed' ? 'bg-green-100 text-green-800 border-green-200' : 
                               ($job->status === 'cancelled' ? 'bg-red-100 text-red-800 border-red-200' : 'bg-blue-100 text-blue-800 border-blue-200') }}">
                            {{ ucfirst(str_replace('_', ' ', $job->status)) }}
                        </span>
                    </div>
                    <div class="text-sm text-slate-600">
                        <p class="text-xs text-slate-500">Amount</p>
                        <p class="font-semibold">@currency($job->quote->total ?? 0)</p>
                    </div>
                    <div class="text-sm text-slate-600">
                        <p class="text-xs text-slate-500">Date</p>
                        <p class="font-semibold">@formatDate($job->created_at)</p>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-slate-500">{{ $job->payments->where('status', 'completed')->count() }} payments</span>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500 text-center py-8">No job history</p>
            @endforelse
        </div>

        @if($jobHistory->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $jobHistory->links() }}
            </div>
        @endif
    </div>

    <div class="flex justify-end">
        <a href="{{ route('admin.customers.index') }}" 
           class="px-6 py-3 bg-slate-100 text-slate-700 rounded-xl hover:bg-slate-200 transition-colors font-medium">
            Back to List
        </a>
    </div>
</div>
@endsection
