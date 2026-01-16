<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['user', 'job', 'ticket', 'approver']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('date_from')) {
            $query->where('expense_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('expense_date', '<=', $request->date_to);
        }

        $expenses = $query->orderBy('expense_date', 'desc')->paginate(20);

        return response()->json($expenses);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_id' => 'nullable|exists:service_jobs,id',
            'ticket_id' => 'nullable|exists:tickets,id',
            'category' => 'required|in:travel,parts,tools,fuel,parking,meals,accommodation,communication,supplies,other',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'expense_date' => 'required|date',
            'payment_method' => 'required|in:cash,card,bank_transfer,mobile_payment,other',
            'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['expense_number'] = 'EXP-' . strtoupper(Str::random(8));
        $validated['status'] = 'pending';

        if ($request->hasFile('receipt_file')) {
            $validated['receipt_file'] = $request->file('receipt_file')->store('expenses/receipts', 'public');
        }

        if ($request->hasFile('attachments')) {
            $attachments = [];
            foreach ($request->file('attachments') as $file) {
                $attachments[] = $file->store('expenses/attachments', 'public');
            }
            $validated['attachments'] = $attachments;
        }

        $expense = Expense::create($validated);

        return response()->json($expense->load(['user', 'job', 'ticket']), 201);
    }

    public function show($id)
    {
        $expense = Expense::with(['user', 'job', 'ticket', 'approver'])->findOrFail($id);
        return response()->json($expense);
    }

    public function approve(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);

        if ($expense->status !== 'pending') {
            return response()->json(['message' => 'Expense is not pending'], 422);
        }

        $expense->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return response()->json($expense->load(['user', 'job', 'ticket', 'approver']));
    }

    public function reject(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $expense->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return response()->json($expense->load(['user', 'job', 'ticket', 'approver']));
    }

    public function markReimbursed($id)
    {
        $expense = Expense::findOrFail($id);

        if ($expense->status !== 'approved') {
            return response()->json(['message' => 'Expense must be approved first'], 422);
        }

        $expense->update(['status' => 'reimbursed']);

        return response()->json($expense->load(['user', 'job', 'ticket', 'approver']));
    }
}
