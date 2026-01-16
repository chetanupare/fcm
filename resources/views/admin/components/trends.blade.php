@extends('layouts.app')

@section('title', 'Component Trends')
@section('page-title', 'Component Requirement Trends')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-2xl font-bold text-slate-800">Component Trends & Analytics</h3>
            <p class="text-sm text-slate-500 mt-1">Track component usage and requirements</p>
        </div>
        <a href="{{ route('admin.components.index') }}" 
           class="px-6 py-3 bg-slate-100 text-slate-700 rounded-xl hover:bg-slate-200 transition-colors font-medium">
            Back to Components
        </a>
    </div>

    <!-- Top Components by Usage -->
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-8">
        <h3 class="text-lg font-bold text-slate-800 mb-6">Most Used Components (Last 90 Days)</h3>
        <div class="space-y-4">
            @forelse($trends as $component)
                <div class="group relative grid grid-cols-6 gap-4 items-center bg-slate-50 p-4 rounded-xl border border-slate-100 shadow-sm hover:shadow-md hover:border-blue-200 transition-all duration-200">
                    <div class="col-span-2 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold text-slate-800">{{ $component->name }}</p>
                            <p class="text-xs text-slate-500">{{ $component->category->name }}</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 mb-1">Total Used</p>
                        <p class="text-xl font-bold text-slate-800">{{ $component->total_used }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 mb-1">Usage Count</p>
                        <p class="text-lg font-semibold text-slate-800">{{ $component->usage_count ?? 0 }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 mb-1">Current Stock</p>
                        <p class="text-lg font-semibold {{ $component->isLowStock() ? 'text-orange-600' : 'text-slate-800' }}">
                            {{ $component->stock_quantity }}
                        </p>
                    </div>
                    <div class="text-right">
                        <a href="{{ route('admin.components.show', $component) }}" 
                           class="px-3 py-1 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors text-sm font-medium">
                            View
                        </a>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500 text-center py-8">No usage data available</p>
            @endforelse
        </div>
    </div>

    <!-- Category Distribution -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4">Components by Category</h3>
            <div class="space-y-3">
                @foreach($categories as $category)
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-slate-700">{{ $category->name }}</span>
                        <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-lg text-sm font-semibold">
                            {{ $category->components_count }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4">Components by Brand</h3>
            <div class="space-y-3">
                @foreach($brands as $brand)
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-slate-700">{{ $brand->name }}</span>
                        <span class="px-3 py-1 bg-purple-50 text-purple-700 rounded-lg text-sm font-semibold">
                            {{ $brand->components_count }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
