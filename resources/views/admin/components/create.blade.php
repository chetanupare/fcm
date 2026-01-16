@extends('layouts.app')

@section('title', 'Create Component')
@section('page-title', 'Create Component')

@section('content')
<div class="max-w-4xl">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-8">
        <div class="mb-6">
            <h3 class="text-2xl font-bold text-slate-800 mb-2">Add New Component</h3>
            <p class="text-sm text-slate-500">Add a component to your inventory</p>
        </div>

        <form method="POST" action="{{ route('admin.components.store') }}" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">Component Name *</label>
                    <input type="text" name="name" id="name" required 
                           class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                           value="{{ old('name') }}" placeholder="e.g., Black Toner Cartridge">
                </div>

                <div>
                    <label for="sku" class="block text-sm font-semibold text-slate-700 mb-2">SKU *</label>
                    <input type="text" name="sku" id="sku" required 
                           class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-mono" 
                           value="{{ old('sku') }}" placeholder="COMP-000001">
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-semibold text-slate-700 mb-2">Description</label>
                <textarea name="description" id="description" rows="3" 
                          class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                          placeholder="Component description...">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="category_id" class="block text-sm font-semibold text-slate-700 mb-2">Category *</label>
                    <select name="category_id" id="category_id" required 
                            class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="brand_id" class="block text-sm font-semibold text-slate-700 mb-2">Brand</label>
                    <select name="brand_id" id="brand_id" 
                            class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <option value="">No Brand</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-6">
                <div>
                    <label for="cost_price" class="block text-sm font-semibold text-slate-700 mb-2">Cost Price *</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">$</span>
                        <input type="number" name="cost_price" id="cost_price" step="0.01" min="0" required 
                               class="w-full border border-slate-300 rounded-xl pl-8 pr-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                               value="{{ old('cost_price') }}">
                    </div>
                </div>

                <div>
                    <label for="selling_price" class="block text-sm font-semibold text-slate-700 mb-2">Selling Price *</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">$</span>
                        <input type="number" name="selling_price" id="selling_price" step="0.01" min="0" required 
                               class="w-full border border-slate-300 rounded-xl pl-8 pr-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                               value="{{ old('selling_price') }}">
                    </div>
                </div>

                <div>
                    <label for="unit" class="block text-sm font-semibold text-slate-700 mb-2">Unit *</label>
                    <input type="text" name="unit" id="unit" required 
                           class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                           value="{{ old('unit', 'piece') }}" placeholder="piece, pack, box">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="stock_quantity" class="block text-sm font-semibold text-slate-700 mb-2">Stock Quantity *</label>
                    <input type="number" name="stock_quantity" id="stock_quantity" min="0" required 
                           class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                           value="{{ old('stock_quantity', 0) }}">
                </div>

                <div>
                    <label for="min_stock_level" class="block text-sm font-semibold text-slate-700 mb-2">Min Stock Level *</label>
                    <input type="number" name="min_stock_level" id="min_stock_level" min="0" required 
                           class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                           value="{{ old('min_stock_level', 5) }}">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="part_number" class="block text-sm font-semibold text-slate-700 mb-2">Part Number</label>
                    <input type="text" name="part_number" id="part_number" 
                           class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                           value="{{ old('part_number') }}">
                </div>

                <div>
                    <label for="oem_part_number" class="block text-sm font-semibold text-slate-700 mb-2">OEM Part Number</label>
                    <input type="text" name="oem_part_number" id="oem_part_number" 
                           class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                           value="{{ old('oem_part_number') }}">
                </div>
            </div>

            <div class="flex items-center gap-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" checked 
                           class="h-4 w-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                    <span class="ml-3 text-sm font-medium text-slate-700">Component is active</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="is_consumable" value="1" 
                           class="h-4 w-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                    <span class="ml-3 text-sm font-medium text-slate-700">Consumable item</span>
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('admin.components.index') }}" 
                   class="px-6 py-3 border border-slate-300 rounded-xl text-slate-700 hover:bg-slate-50 transition-all font-medium">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all font-medium shadow-lg hover:shadow-xl">
                    Create Component
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
