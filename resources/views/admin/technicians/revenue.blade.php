@extends('layouts.app')

@section('title', 'Technician Revenue')
@section('page-title', 'Revenue Report - ' . $technician->user->name)

@section('content')
<div class="space-y-6">
    <!-- Revenue Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-xl p-6 text-white">
            <p class="text-sm font-medium text-blue-100 mb-2">Total Revenue</p>
            <p class="text-4xl font-bold">@currency($revenue)</p>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-xl p-6 text-white">
            <p class="text-sm font-medium text-purple-100 mb-2">Commission Rate</p>
            <p class="text-4xl font-bold">{{ $technician->commission_rate }}%</p>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-xl p-6 text-white">
            <p class="text-sm font-medium text-green-100 mb-2">Commission Amount</p>
            <p class="text-4xl font-bold">@currency($revenue * ($technician->commission_rate / 100))</p>
        </div>
    </div>

    <!-- Technician Details -->
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-8">
        <h3 class="text-xl font-bold text-slate-800 mb-6">Technician Details</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div>
                <dt class="text-sm font-medium text-slate-500 mb-1">Name</dt>
                <dd class="text-lg font-semibold text-slate-800">{{ $technician->user->name }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-slate-500 mb-1">Email</dt>
                <dd class="text-lg font-semibold text-slate-800">{{ $technician->user->email }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-slate-500 mb-1">Status</dt>
                <dd class="text-lg font-semibold text-slate-800">
                    <span class="px-3 py-1 rounded-full text-sm {{ $technician->status === 'on_duty' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-700' }}">
                        {{ ucfirst(str_replace('_', ' ', $technician->status)) }}
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-slate-500 mb-1">Active Jobs</dt>
                <dd class="text-lg font-semibold text-slate-800">{{ $technician->active_jobs_count }}</dd>
            </div>
        </div>
    </div>

    <div class="flex justify-end">
        <a href="{{ route('admin.technicians.index') }}" 
           class="px-6 py-3 bg-slate-100 text-slate-700 rounded-xl hover:bg-slate-200 transition-colors font-medium">
            Back to List
        </a>
    </div>
</div>
@endsection
