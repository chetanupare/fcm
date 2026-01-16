<?php

namespace App\Services\Finance;

use App\Models\Payment;
use App\Models\Job;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentReconciliationService
{
    /**
     * Generate daily reconciliation report
     * 
     * @param Carbon|null $date
     * @return array
     */
    public function generateDailyReconciliation(?Carbon $date = null): array
    {
        $date = $date ?? now();
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        // Get all payments for the day
        $payments = Payment::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->with(['job.ticket.customer', 'quote'])
            ->get();

        // Cash payments
        $cashPayments = $payments->where('method', 'cash')->where('status', 'completed');
        $cashCollected = $cashPayments->sum('amount');
        $cashCount = $cashPayments->count();

        // Online payments
        $onlinePayments = $payments->whereIn('method', ['razorpay', 'phonepe', 'paytm', 'stripe', 'paypal'])
            ->where('status', 'completed');
        $onlineCollected = $onlinePayments->sum('amount');
        $onlineCount = $onlinePayments->count();

        // Total expected (from completed jobs with quotes)
        $completedJobs = Job::whereBetween('payment_received_at', [$startOfDay, $endOfDay])
            ->whereHas('quote')
            ->with('quote')
            ->get();
        
        $expectedTotal = $completedJobs->sum(function ($job) {
            return $job->quote->total ?? 0;
        });

        // Actual collected
        $actualCollected = $cashCollected + $onlineCollected;

        // Outstanding receivables (jobs completed but not paid)
        $outstandingJobs = Job::where('status', 'completed')
            ->whereNull('payment_received_at')
            ->whereHas('quote')
            ->with('quote')
            ->get();
        
        $outstandingAmount = $outstandingJobs->sum(function ($job) {
            return $job->quote->total ?? 0;
        });

        // Unmatched payments (payments without jobs or jobs without payments)
        $unmatchedPayments = $payments->filter(function ($payment) {
            return !$payment->job || !$payment->job->quote;
        });

        return [
            'date' => $date->format('Y-m-d'),
            'cash' => [
                'collected' => $cashCollected,
                'count' => $cashCount,
                'payments' => $cashPayments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => $payment->amount,
                        'job_id' => $payment->job_id,
                        'customer' => $payment->job->ticket->customer->name ?? 'N/A',
                        'created_at' => $payment->created_at,
                    ];
                }),
            ],
            'online' => [
                'collected' => $onlineCollected,
                'count' => $onlineCount,
                'payments' => $onlinePayments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => $payment->amount,
                        'method' => $payment->method,
                        'transaction_id' => $payment->transaction_id,
                        'job_id' => $payment->job_id,
                        'customer' => $payment->job->ticket->customer->name ?? 'N/A',
                        'created_at' => $payment->created_at,
                    ];
                }),
            ],
            'summary' => [
                'expected_total' => $expectedTotal,
                'actual_collected' => $actualCollected,
                'difference' => $actualCollected - $expectedTotal,
                'outstanding_amount' => $outstandingAmount,
                'outstanding_count' => $outstandingJobs->count(),
            ],
            'exceptions' => [
                'unmatched_payments' => $unmatchedPayments->count(),
                'unmatched_payments_list' => $unmatchedPayments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => $payment->amount,
                        'method' => $payment->method,
                        'created_at' => $payment->created_at,
                    ];
                }),
            ],
        ];
    }

    /**
     * Match payments to jobs
     * 
     * @param Payment $payment
     * @return bool
     */
    public function matchPaymentToJob(Payment $payment): bool
    {
        if ($payment->job_id) {
            return true; // Already matched
        }

        // Try to match by transaction ID or amount
        // This is a simplified version - in production, use more sophisticated matching
        return false;
    }

    /**
     * Flag unmatched payments
     * 
     * @param Carbon|null $date
     * @return array
     */
    public function flagUnmatchedPayments(?Carbon $date = null): array
    {
        $date = $date ?? now();
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        $payments = Payment::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->whereNull('job_id')
            ->get();

        return $payments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'amount' => $payment->amount,
                'method' => $payment->method,
                'transaction_id' => $payment->transaction_id,
                'created_at' => $payment->created_at,
            ];
        })->toArray();
    }
}
