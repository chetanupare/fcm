<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PosTransaction;
use App\Models\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $query = PosTransaction::with(['user', 'customer']);

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($transactions);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:users,id',
            'transaction_type' => 'required|in:sale,return,exchange',
            'items' => 'required|array|min:1',
            'items.*.component_id' => 'required|exists:components,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,card,mobile_payment,bank_transfer,credit',
            'paid_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Calculate totals
        $subtotal = collect($validated['items'])->sum(function($item) {
            return $item['quantity'] * $item['price'];
        });

        $taxAmount = ($subtotal * ($validated['tax_rate'] ?? 0)) / 100;
        $totalAmount = $subtotal + $taxAmount - ($validated['discount_amount'] ?? 0);
        $changeAmount = max(0, $validated['paid_amount'] - $totalAmount);

        $validated['transaction_number'] = 'POS-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        $validated['user_id'] = $request->user()->id;
        $validated['subtotal'] = $subtotal;
        $validated['tax_amount'] = $taxAmount;
        $validated['total_amount'] = $totalAmount;
        $validated['change_amount'] = $changeAmount;
        $validated['status'] = 'completed';

        // Update inventory
        foreach ($validated['items'] as $item) {
            $component = Component::find($item['component_id']);
            if ($component && $validated['transaction_type'] === 'sale') {
                $component->decrement('stock_quantity', $item['quantity']);
                $component->incrementUsage($item['quantity']);
            } elseif ($component && $validated['transaction_type'] === 'return') {
                $component->increment('stock_quantity', $item['quantity']);
            }
        }

        $transaction = PosTransaction::create($validated);

        return response()->json($transaction->load(['user', 'customer']), 201);
    }

    public function show($id)
    {
        $transaction = PosTransaction::with(['user', 'customer'])->findOrFail($id);
        return response()->json($transaction);
    }

    public function scanBarcode(Request $request)
    {
        $validated = $request->validate([
            'barcode' => 'required|string',
        ]);

        $component = Component::where('barcode', $validated['barcode'])->first();

        if (!$component) {
            return response()->json(['message' => 'Component not found'], 404);
        }

        return response()->json($component->load(['category', 'brand', 'supplier']));
    }
}
