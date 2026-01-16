@extends('layouts.app')

@section('title', 'Component Details')
@section('page-title', 'Component Details - ' . $component->name)

@section('content')
<div class="space-y-6">
    <!-- Component Info -->
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-8">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h3 class="text-2xl font-bold text-slate-800">{{ $component->name }}</h3>
                <p class="text-slate-500 mt-1">SKU: {{ $component->sku }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.components.edit', $component) }}" 
                   class="px-4 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors font-medium">
                    Edit
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-xs text-slate-500 mb-1">Category</p>
                <p class="text-lg font-bold text-slate-800">{{ $component->category->name }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-xs text-slate-500 mb-1">Brand</p>
                <p class="text-lg font-bold text-slate-800">{{ $component->brand->name ?? 'N/A' }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-xs text-slate-500 mb-1">Stock</p>
                <p class="text-2xl font-bold {{ $component->isLowStock() ? 'text-orange-600' : 'text-slate-800' }}">
                    {{ $component->stock_quantity }}
                </p>
            </div>
            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-xs text-slate-500 mb-1">Total Used</p>
                <p class="text-2xl font-bold text-slate-800">{{ $component->total_used }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-6 mt-6">
            <div>
                <p class="text-xs text-slate-500 mb-1">Cost Price</p>
                <p class="text-xl font-bold text-slate-800">@currency($component->cost_price)</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 mb-1">Selling Price</p>
                <p class="text-xl font-bold text-slate-800">@currency($component->selling_price)</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 mb-1">Profit Margin</p>
                <p class="text-xl font-bold text-green-600">{{ number_format($component->profit_margin, 1) }}%</p>
            </div>
        </div>

        @if($component->description)
            <div class="mt-6 pt-6 border-t border-slate-100">
                <p class="text-sm text-slate-700">{{ $component->description }}</p>
            </div>
        @endif
    </div>

    <!-- Usage Trends Chart -->
    @if($usageTrends->count() > 0)
        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4">Usage Trends (Last 30 Days)</h3>
            <canvas id="usageChart" height="60"></canvas>
        </div>
    @endif

    <div class="flex justify-end">
        <a href="{{ route('admin.components.index') }}" 
           class="px-6 py-3 bg-slate-100 text-slate-700 rounded-xl hover:bg-slate-200 transition-colors font-medium">
            Back to List
        </a>
    </div>
</div>
@endsection

@push('scripts')
@if($usageTrends->count() > 0)
<script>
    const usageData = @json($usageTrends);
    const ctx = document.getElementById('usageChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: usageData.map(d => d.date),
            datasets: [{
                label: 'Quantity Used',
                data: usageData.map(d => parseInt(d.total)),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                }
            }
        }
    });
</script>
@endif
@endpush
