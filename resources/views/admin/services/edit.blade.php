@extends('layouts.app')

@section('title', 'Edit Service')
@section('page-title', 'Edit Service')

@section('content')
<div class="max-w-3xl">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-8">
        <div class="mb-6">
            <h3 class="text-2xl font-bold text-slate-800 mb-2">Edit Service</h3>
            <p class="text-sm text-slate-500">Update service details</p>
        </div>

        <form method="POST" action="{{ route('admin.services.update', $service) }}" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">Service Name *</label>
                    <input type="text" name="name" id="name" required 
                           class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                           value="{{ old('name', $service->name) }}">
                </div>

                <div>
                    <label for="price" class="block text-sm font-semibold text-slate-700 mb-2">Price *</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">$</span>
                        <input type="number" name="price" id="price" step="0.01" min="0" required 
                               class="w-full border border-slate-300 rounded-xl pl-8 pr-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                               value="{{ old('price', $service->price) }}">
                    </div>
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-semibold text-slate-700 mb-2">Description</label>
                <textarea name="description" id="description" rows="3" 
                          class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">{{ old('description', $service->description) }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="device_type" class="block text-sm font-semibold text-slate-700 mb-2">Device Type</label>
                    <select name="device_type" id="device_type" 
                            class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <option value="">Universal</option>
                        <option value="laptop" {{ $service->device_type === 'laptop' ? 'selected' : '' }}>Laptop</option>
                        <option value="phone" {{ $service->device_type === 'phone' ? 'selected' : '' }}>Phone</option>
                        <option value="ac" {{ $service->device_type === 'ac' ? 'selected' : '' }}>AC</option>
                        <option value="fridge" {{ $service->device_type === 'fridge' ? 'selected' : '' }}>Fridge</option>
                        <option value="tablet" {{ $service->device_type === 'tablet' ? 'selected' : '' }}>Tablet</option>
                        <option value="desktop" {{ $service->device_type === 'desktop' ? 'selected' : '' }}>Desktop</option>
                        <option value="other" {{ $service->device_type === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div>
                    <label for="category" class="block text-sm font-semibold text-slate-700 mb-2">Category *</label>
                    <select name="category" id="category" required 
                            class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <option value="repair" {{ $service->category === 'repair' ? 'selected' : '' }}>Repair</option>
                        <option value="diagnosis" {{ $service->category === 'diagnosis' ? 'selected' : '' }}>Diagnosis</option>
                        <option value="part" {{ $service->category === 'part' ? 'selected' : '' }}>Part</option>
                        <option value="visit_fee" {{ $service->category === 'visit_fee' ? 'selected' : '' }}>Visit Fee</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ $service->is_active ? 'checked' : '' }} 
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
                    Update Service
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
