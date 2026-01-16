@extends('layouts.app')

@section('title', 'Components')
@section('page-title', 'Component Management')

@section('content')
<div class="space-y-6" x-data="{ filters: { category: '{{ request('category_id') }}', brand: '{{ request('brand_id') }}', lowStock: {{ request('low_stock') ? 'true' : 'false' }} } }">
    <!-- Export Buttons -->
    <div class="flex items-center justify-end gap-2 mb-4">
        <a href="{{ route('admin.export.components', ['format' => 'csv']) }}" 
           class="px-4 py-2 bg-green-50 text-green-700 rounded-xl hover:bg-green-100 transition-all font-medium border border-green-200 flex items-center gap-2 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
            </svg>
            Export CSV
        </a>
        <a href="{{ route('admin.export.components', ['format' => 'excel']) }}" 
           class="px-4 py-2 bg-blue-50 text-blue-700 rounded-xl hover:bg-blue-100 transition-all font-medium border border-blue-200 flex items-center gap-2 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
            </svg>
            Export Excel
        </a>
    </div>

    <!-- Header with Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-xl p-6 text-white">
            <p class="text-sm font-medium text-blue-100 mb-2">Total Components</p>
            <p class="text-4xl font-bold">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-xl p-6 text-white">
            <p class="text-sm font-medium text-green-100 mb-2">Active</p>
            <p class="text-4xl font-bold">{{ $stats['active'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl shadow-xl p-6 text-white">
            <p class="text-sm font-medium text-orange-100 mb-2">Low Stock</p>
            <p class="text-4xl font-bold">{{ $stats['low_stock'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-xl p-6 text-white">
            <p class="text-sm font-medium text-purple-100 mb-2">Inventory Value</p>
            <p class="text-2xl font-bold">@currency($stats['total_value'])</p>
        </div>
    </div>

    <!-- Filters & Actions -->
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-slate-800">Components Inventory</h3>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.components.trends') }}" 
                   class="px-4 py-2 bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100 transition-colors font-medium text-sm">
                    View Trends
                </a>
                <a href="{{ route('admin.components.create') }}" 
                   class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all font-medium shadow-lg hover:shadow-xl flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Component
                </a>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.components.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search components..." 
                       class="w-full border border-slate-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
            </div>
            <div>
                <select name="category_id" class="w-full border border-slate-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="brand_id" class="w-full border border-slate-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    <option value="">All Brands</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <label class="flex items-center">
                    <input type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }} 
                           class="h-4 w-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                    <span class="ml-2 text-sm text-slate-700">Low Stock Only</span>
                </label>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Components Table -->
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-slate-50 to-white border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Component</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Brand</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Stock</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Cost</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Margin</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($components as $component)
                        <tr class="hover:bg-slate-50 transition-colors ticket-enter">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3 min-w-[200px]">
                                    <div class="h-10 w-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 flex-shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold text-slate-800 text-sm truncate">{{ $component->name }}</p>
                                        <p class="text-xs text-slate-500 truncate">SKU: {{ $component->sku }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200 whitespace-nowrap">
                                    {{ $component->category->name }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-slate-600 whitespace-nowrap">
                                    {{ $component->brand->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <span class="font-semibold text-slate-800 {{ $component->isLowStock() ? 'text-orange-600' : '' }} whitespace-nowrap">
                                        {{ $component->stock_quantity }}
                                    </span>
                                    @if($component->isLowStock())
                                        <span class="px-2 py-0.5 bg-orange-100 text-orange-700 rounded text-xs font-medium whitespace-nowrap">
                                            Low
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-mono text-slate-600 whitespace-nowrap">@currency($component->cost_price)</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-mono font-semibold text-slate-800 whitespace-nowrap">@currency($component->selling_price)</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-green-100 text-green-700 border border-green-200 whitespace-nowrap">
                                    {{ number_format($component->profit_margin, 1) }}%
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.components.show', $component) }}" 
                                       class="px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors text-sm font-medium whitespace-nowrap">
                                        View
                                    </a>
                                    <a href="{{ route('admin.components.edit', $component) }}" 
                                       class="px-3 py-1.5 bg-slate-50 text-slate-700 rounded-lg hover:bg-slate-100 transition-colors text-sm font-medium whitespace-nowrap">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                <p class="text-slate-500 text-lg font-medium">No components found</p>
                                <a href="{{ route('admin.components.create') }}" class="mt-4 inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    Create First Component
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($components->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
                {{ $components->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
