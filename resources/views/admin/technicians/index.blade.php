@extends('layouts.app')

@section('title', 'Technicians')
@section('page-title', 'Technicians')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-2xl font-bold text-slate-800">Technician Management</h3>
            <p class="text-sm text-slate-500 mt-1">Monitor technician status and performance</p>
        </div>
        <a href="{{ route('admin.technicians.map') }}" 
           class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all font-medium shadow-lg hover:shadow-xl flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
            </svg>
            View Live Map
        </a>
    </div>

    <!-- Technicians Grid - Floating Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($technicians as $tech)
            <div class="group relative bg-white rounded-2xl border border-slate-100 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden ticket-enter">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-blue-500 to-blue-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-4">
                            <div class="h-14 w-14 rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center text-white shadow-lg text-lg font-bold">
                                {{ strtoupper(substr($tech['name'], 0, 2)) }}
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-slate-800">{{ $tech['name'] }}</h4>
                                <p class="text-xs text-slate-500">{{ $tech['email'] }}</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold border shadow-sm
                            {{ $tech['status'] === 'on_duty' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-slate-50 text-slate-600 border-slate-200' }}">
                            @if($tech['status'] === 'on_duty')
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5 inline-block animate-pulse"></span>
                            @endif
                            {{ ucfirst(str_replace('_', ' ', $tech['status'])) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="bg-slate-50 rounded-xl p-3">
                            <p class="text-xs text-slate-500 mb-1">Active Jobs</p>
                            <p class="text-2xl font-bold text-slate-800">{{ $tech['active_jobs_count'] }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-3">
                            <p class="text-xs text-slate-500 mb-1">Total Revenue</p>
                            <p class="text-xl font-bold text-slate-800">@currency($tech['total_revenue'])</p>
                        </div>
                    </div>

                    @if($tech['phone'])
                        <div class="flex items-center gap-2 text-sm text-slate-600 mb-4">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            {{ $tech['phone'] }}
                        </div>
                    @endif

                    <a href="{{ route('admin.technicians.revenue', $tech['id']) }}" 
                       class="block w-full px-4 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors font-medium text-sm text-center">
                        View Revenue Report
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-slate-100 shadow-lg p-12 text-center">
                <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <p class="text-slate-500 text-lg font-medium">No technicians found</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
