<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $suppliers = $query->orderBy('name')->paginate(20);

        return response()->json($suppliers);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'required|string',
            'alternate_phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'website' => 'nullable|url',
            'tax_id' => 'nullable|string',
            'payment_terms' => 'required|in:net_15,net_30,net_45,net_60,cod,prepaid',
            'credit_limit' => 'nullable|numeric|min:0',
            'status' => 'sometimes|in:active,inactive,suspended',
            'specializations' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $supplier = Supplier::create($validated);

        return response()->json($supplier, 201);
    }

    public function show($id)
    {
        $supplier = Supplier::with('purchaseOrders')->findOrFail($id);
        return response()->json($supplier);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'sometimes|string',
            'alternate_phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'website' => 'nullable|url',
            'tax_id' => 'nullable|string',
            'payment_terms' => 'sometimes|in:net_15,net_30,net_45,net_60,cod,prepaid',
            'credit_limit' => 'nullable|numeric|min:0',
            'status' => 'sometimes|in:active,inactive,suspended',
            'specializations' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $supplier->update($validated);

        return response()->json($supplier);
    }
}
