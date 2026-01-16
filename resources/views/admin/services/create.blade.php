@extends('layouts.app')

@section('title', 'Create Service')
@section('page-title', 'Create Service')

@section('content')
<div class="max-w-3xl">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-8">
        <div class="mb-6">
            <h3 class="text-2xl font-bold text-slate-800 mb-2">Create New Service</h3>
            <p class="text-sm text-slate-500">Add a new service to your catalog</p>
        </div>

        <form method="POST" action="{{ route('admin.services.store') }}" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">Service Name *</label>
                    <input type="text" name="name" id="name" required 
                           class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                           value="{{ old('name') }}" placeholder="e.g., Motherboard Repair">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="price" class="block text-sm font-semibold text-slate-700 mb-2">Price *</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">$</span>
                        <input type="number" name="price" id="price" step="0.01" min="0" required 
                               class="w-full border border-slate-300 rounded-xl pl-8 pr-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                               value="{{ old('price') }}" placeholder="0.00">
                    </div>
                    @error('price')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-semibold text-slate-700 mb-2">Description</label>
                <textarea name="description" id="description" rows="3" 
                          class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                          placeholder="Service description...">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="device_type" class="block text-sm font-semibold text-slate-700 mb-2">Device Type</label>
                    <select name="device_type" id="device_type" 
                            class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <option value="">Universal (All Devices)</option>
                        <option value="laptop">Laptop</option>
                        <option value="phone">Phone</option>
                        <option value="ac">AC</option>
                        <option value="fridge">Fridge</option>
                        <option value="tablet">Tablet</option>
                        <option value="desktop">Desktop</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div>
                    <label for="category" class="block text-sm font-semibold text-slate-700 mb-2">Category *</label>
                    <select name="category" id="category" required 
                            class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <option value="repair">Repair</option>
                        <option value="diagnosis">Diagnosis</option>
                        <option value="part">Part</option>
                        <option value="visit_fee">Visit Fee</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="is_active" value="1" checked 
                       class="h-4 w-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                <label for="is_active" class="ml-3 text-sm font-medium text-slate-700">Service is active</label>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('admin.services.index') }}" 
                   class="px-6 py-3 border border-slate-300 rounded-xl text-slate-700 hover:bg-slate-50 transition-all font-medium">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all font-medium shadow-lg hover:shadow-xl">
                    Create Service
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
