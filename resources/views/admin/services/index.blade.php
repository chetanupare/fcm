@extends('layouts.app')

@section('title', 'Service Catalog')
@section('page-title', 'Service Catalog')

@section('content')
<div class="space-y-6" x-data="{ deleteModal: false, serviceToDelete: null }">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-2xl font-bold text-slate-800">Service Catalog</h3>
            <p class="text-sm text-slate-500 mt-1">Manage your service offerings and pricing</p>
        </div>
        <a href="{{ route('admin.services.create') }}" 
           class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all font-medium shadow-lg hover:shadow-xl flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add New Service
        </a>
    </div>

    <!-- Services Grid - Floating Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($services as $service)
            <div class="group relative bg-white rounded-2xl border border-slate-100 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden ticket-enter">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-blue-500 to-blue-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h4 class="text-lg font-bold text-slate-800 mb-1">{{ $service->name }}</h4>
                            @if($service->description)
                                <p class="text-sm text-slate-500 line-clamp-2">{{ $service->description }}</p>
                            @endif
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold border shadow-sm
                            {{ $service->is_active ? 'bg-green-50 text-green-700 border-green-200' : 'bg-slate-50 text-slate-600 border-slate-200' }}">
                            {{ $service->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-xs text-slate-500">Price</p>
                            <p class="text-2xl font-bold text-slate-800">@currency($service->price)</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-slate-500">Category</p>
                            <p class="text-sm font-semibold text-slate-700">{{ ucfirst(str_replace('_', ' ', $service->category)) }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 mb-4">
                        <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs font-medium">
                            {{ $service->device_type ? ucfirst($service->device_type) : 'Universal' }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2 pt-4 border-t border-slate-100">
                        <a href="{{ route('admin.services.edit', $service) }}" 
                           class="flex-1 px-4 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors font-medium text-sm text-center">
                            Edit
                        </a>
                        <button @click="deleteModal = true; serviceToDelete = {{ $service->id }}" 
                                class="flex-1 px-4 py-2 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition-colors font-medium text-sm">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-slate-100 shadow-lg p-12 text-center">
                <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <p class="text-slate-500 text-lg font-medium">No services found</p>
                <a href="{{ route('admin.services.create') }}" class="mt-4 inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Create First Service
                </a>
            </div>
        @endforelse
    </div>
</div>

<!-- Delete Modal -->
<div x-show="deleteModal" 
     x-cloak
     @click.away="deleteModal = false"
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 transform transition-all ticket-enter">
        <h3 class="text-xl font-bold text-slate-800 mb-4">Delete Service?</h3>
        <p class="text-slate-600 mb-6">This action cannot be undone. Are you sure you want to delete this service?</p>
        <div class="flex justify-end gap-3">
            <button @click="deleteModal = false" 
                    class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors font-medium">
                Cancel
            </button>
            <form :action="`/admin/services/${serviceToDelete}`" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
