@extends('layouts.app')

@section('title', 'Edit Customer')
@section('page-title', 'Edit Customer')

@section('content')
<div class="max-w-3xl">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-8">
        <div class="mb-6">
            <h3 class="text-2xl font-bold text-slate-800 mb-2">Edit Customer</h3>
            <p class="text-sm text-slate-500">Update customer information</p>
        </div>

        <form method="POST" action="{{ route('admin.customers.update', $customer) }}" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">Name *</label>
                    <input type="text" name="name" id="name" required 
                           class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                           value="{{ old('name', $customer->name) }}">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">Email *</label>
                    <input type="email" name="email" id="email" required 
                           class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                           value="{{ old('email', $customer->email) }}">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="phone" class="block text-sm font-semibold text-slate-700 mb-2">Phone</label>
                    <input type="text" name="phone" id="phone" 
                           class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                           value="{{ old('phone', $customer->phone) }}">
                </div>

                <div>
                    <label for="currency_preference" class="block text-sm font-semibold text-slate-700 mb-2">Currency</label>
                    <select name="currency_preference" id="currency_preference" 
                            class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <option value="USD" {{ $customer->currency_preference === 'USD' ? 'selected' : '' }}>USD</option>
                        <option value="EUR" {{ $customer->currency_preference === 'EUR' ? 'selected' : '' }}>EUR</option>
                        <option value="INR" {{ $customer->currency_preference === 'INR' ? 'selected' : '' }}>INR</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('admin.customers.show', $customer) }}" 
                   class="px-6 py-3 border border-slate-300 rounded-xl text-slate-700 hover:bg-slate-50 transition-all font-medium">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all font-medium shadow-lg hover:shadow-xl">
                    Update Customer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
