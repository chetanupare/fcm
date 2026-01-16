<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['customer', 'job', 'quote', 'amcContract']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->has('overdue')) {
            $query->where('due_date', '<', now()->toDateString())
                  ->whereNotIn('status', ['paid', 'cancelled']);
        }

        $invoices = $query->orderBy('invoice_date', 'desc')->paginate(20);

        return response()->json($invoices);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'job_id' => 'nullable|exists:service_jobs,id',
            'quote_id' => 'nullable|exists:quotes,id',
            'amc_contract_id' => 'nullable|exists:amc_contracts,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'subtotal' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'line_items' => 'required|array',
        ]);

        $validated['invoice_number'] = 'INV-' . date('Y') . '-' . strtoupper(Str::random(6));
        $validated['tax_amount'] = ($validated['subtotal'] * ($validated['tax_rate'] ?? 0)) / 100;
        $validated['total_amount'] = $validated['subtotal'] + $validated['tax_amount'] - ($validated['discount_amount'] ?? 0);
        $validated['status'] = 'draft';
        $validated['paid_amount'] = 0;

        $invoice = Invoice::create($validated);

        return response()->json($invoice->load(['customer', 'job', 'quote']), 201);
    }

    public function fromQuote(Request $request, $quoteId)
    {
        $quote = Quote::with(['job', 'job.ticket'])->findOrFail($quoteId);

        $invoice = Invoice::create([
            'customer_id' => $quote->job->ticket->customer_id,
            'job_id' => $quote->job_id,
            'quote_id' => $quote->id,
            'invoice_number' => 'INV-' . date('Y') . '-' . strtoupper(Str::random(6)),
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => $quote->subtotal,
            'tax_rate' => $quote->tax_rate ?? 0,
            'tax_amount' => $quote->tax_amount ?? 0,
            'discount_amount' => $quote->discount_amount ?? 0,
            'total_amount' => $quote->total_amount,
            'paid_amount' => 0,
            'status' => 'draft',
            'line_items' => $quote->items ?? [],
            'currency' => $quote->currency ?? 'USD',
        ]);

        return response()->json($invoice->load(['customer', 'job', 'quote']), 201);
    }

    public function show($id)
    {
        $invoice = Invoice::with(['customer', 'job', 'quote', 'amcContract'])->findOrFail($id);
        return response()->json($invoice);
    }

    public function update(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        $validated = $request->validate([
            'invoice_date' => 'sometimes|date',
            'due_date' => 'sometimes|date|after_or_equal:invoice_date',
            'status' => 'sometimes|in:draft,sent,paid,overdue,cancelled,partially_paid',
            'subtotal' => 'sometimes|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'line_items' => 'sometimes|array',
        ]);

        if (isset($validated['subtotal'])) {
            $validated['tax_amount'] = ($validated['subtotal'] * ($validated['tax_rate'] ?? $invoice->tax_rate)) / 100;
            $validated['total_amount'] = $validated['subtotal'] + $validated['tax_amount'] - ($validated['discount_amount'] ?? $invoice->discount_amount);
        }

        if (isset($validated['paid_amount'])) {
            if ($validated['paid_amount'] >= $invoice->total_amount) {
                $validated['status'] = 'paid';
                $validated['paid_at'] = now();
            } elseif ($validated['paid_amount'] > 0) {
                $validated['status'] = 'partially_paid';
            }
        }

        $invoice->update($validated);

        return response()->json($invoice->load(['customer', 'job', 'quote']));
    }

    public function markSent($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->update(['status' => 'sent', 'sent_at' => now()]);
        return response()->json($invoice);
    }

    public function recordPayment(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $newPaidAmount = $invoice->paid_amount + $validated['amount'];

        if ($newPaidAmount >= $invoice->total_amount) {
            $invoice->update([
                'paid_amount' => $invoice->total_amount,
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        } else {
            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'status' => 'partially_paid',
            ]);
        }

        return response()->json($invoice->load(['customer', 'job', 'quote']));
    }
}
