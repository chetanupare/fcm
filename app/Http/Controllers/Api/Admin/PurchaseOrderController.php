<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'creator']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $orders = $query->orderBy('order_date', 'desc')->paginate(20);

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after:order_date',
            'line_items' => 'required|array|min:1',
            'line_items.*.component_id' => 'required|exists:components,id',
            'line_items.*.quantity' => 'required|integer|min:1',
            'line_items.*.unit_price' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'shipping_cost' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
        ]);

        // Calculate totals
        $subtotal = collect($validated['line_items'])->sum(function($item) {
            return $item['quantity'] * $item['unit_price'];
        });

        $taxAmount = ($subtotal * ($validated['tax_rate'] ?? 0)) / 100;
        $totalAmount = $subtotal + $taxAmount + ($validated['shipping_cost'] ?? 0);

        $validated['po_number'] = 'PO-' . date('Y') . '-' . strtoupper(Str::random(6));
        $validated['created_by'] = $request->user()->id;
        $validated['subtotal'] = $subtotal;
        $validated['tax_amount'] = $taxAmount;
        $validated['total_amount'] = $totalAmount;
        $validated['status'] = 'draft';

        $purchaseOrder = PurchaseOrder::create($validated);

        return response()->json($purchaseOrder->load(['supplier', 'creator']), 201);
    }

    public function show($id)
    {
        $order = PurchaseOrder::with(['supplier', 'creator'])->findOrFail($id);
        return response()->json($order);
    }

    public function update(Request $request, $id)
    {
        $order = PurchaseOrder::findOrFail($id);

        $validated = $request->validate([
            'status' => 'sometimes|in:draft,sent,acknowledged,partially_received,received,cancelled,closed',
            'expected_delivery_date' => 'nullable|date',
            'actual_delivery_date' => 'nullable|date',
        ]);

        if (isset($validated['status']) && $validated['status'] === 'received') {
            $validated['actual_delivery_date'] = now();
            
            // Update inventory when received
            if ($order->line_items) {
                foreach ($order->line_items as $item) {
                    if (isset($item['component_id'])) {
                        $component = Component::find($item['component_id']);
                        if ($component) {
                            $component->increment('stock_quantity', $item['quantity']);
                            $component->update(['last_reorder_date' => now(), 'alert_sent' => false]);
                        }
                    }
                }
            }
        }

        $order->update($validated);

        return response()->json($order->load(['supplier', 'creator']));
    }

    public function markSent($id)
    {
        $order = PurchaseOrder::findOrFail($id);
        $order->update(['status' => 'sent']);
        return response()->json($order);
    }
}
