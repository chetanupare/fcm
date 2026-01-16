<?php

namespace App\Services\Workflow;

use App\Models\Job;
use App\Models\Payment;
use App\Models\Invoice;
use App\Services\Notification\MultiChannelNotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AutomaticJobClosureService
{
    protected MultiChannelNotificationService $notificationService;

    public function __construct(MultiChannelNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Check if job should be automatically closed and close it
     * 
     * @param Job $job
     * @return bool True if job was closed, false otherwise
     */
    public function checkAndCloseJob(Job $job): bool
    {
        $job = $job->fresh(['payments', 'quote', 'ticket.customer']);

        if (!$job->quote) {
            return false; // No quote, can't determine if payment is complete
        }

        $totalDue = $job->quote->total_amount;
        $totalPaid = $job->payments()
            ->where('status', 'completed')
            ->sum('amount');

        // Check if payment is complete (with small tolerance for rounding)
        if ($totalPaid < ($totalDue - 0.01)) {
            return false; // Payment not complete
        }

        // Payment is complete - close the job
        return $this->closeJob($job);
    }

    /**
     * Close a job automatically
     * 
     * @param Job $job
     * @return bool
     */
    protected function closeJob(Job $job): bool
    {
        if ($job->status === 'completed' && $job->released_at) {
            return false; // Already closed
        }

        DB::transaction(function () use ($job) {
            // Mark job as completed
            $job->update([
                'status' => 'completed',
                'payment_received_at' => now(),
            ]);

            // Release technician
            if ($job->technician) {
                $job->technician->decrementActiveJobs();
            }

            // Generate invoice if not exists
            $this->generateInvoiceIfNeeded($job);

            // Send completion confirmation to customer
            $this->sendCompletionConfirmation($job);

            // Update SLA tracking
            $slaTrackingService = app(SlaTrackingService::class);
            $slaTrackingService->updateSlaStatus($job->ticket);
        });

        Log::info('Job automatically closed', [
            'job_id' => $job->id,
            'ticket_id' => $job->ticket_id,
        ]);

        return true;
    }

    /**
     * Generate invoice if needed
     */
    protected function generateInvoiceIfNeeded(Job $job): void
    {
        // Check if invoice already exists
        $existingInvoice = Invoice::where('job_id', $job->id)->first();
        
        if ($existingInvoice) {
            return; // Invoice already exists
        }

        // Create invoice
        Invoice::create([
            'customer_id' => $job->ticket->customer_id,
            'job_id' => $job->id,
            'quote_id' => $job->quote_id,
            'invoice_number' => $this->generateInvoiceNumber(),
            'total_amount' => $job->quote->total_amount,
            'status' => 'sent',
            'due_date' => now()->addDays(30),
            'issued_at' => now(),
        ]);
    }

    /**
     * Generate unique invoice number
     */
    protected function generateInvoiceNumber(): string
    {
        $prefix = \App\Models\Setting::get('invoice_prefix', 'INV');
        $year = now()->year;
        $month = now()->format('m');
        
        $lastInvoice = Invoice::whereYear('created_at', $year)
            ->whereMonth('created_at', now()->month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastInvoice ? ((int) substr($lastInvoice->invoice_number, -4)) + 1 : 1;
        
        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $sequence);
    }

    /**
     * Send completion confirmation to customer
     */
    protected function sendCompletionConfirmation(Job $job): void
    {
        if (!$job->ticket || !$job->ticket->customer) {
            return;
        }

        $customer = $job->ticket->customer;
        $totalPaid = $job->payments()->where('status', 'completed')->sum('amount');

        $this->notificationService->send(
            $customer,
            'service_complete',
            'Service Complete!',
            'Your service has been completed successfully. Thank you for choosing us!',
            [
                'job_id' => $job->id,
                'ticket_id' => $job->ticket_id,
                'total_amount' => number_format($totalPaid, 2),
                'completed_at' => now()->format('M d, Y'),
                'rating_url' => url("/customer/tracking?ticket={$job->ticket_id}"),
            ]
        );
    }

    /**
     * Handle payment verification and closure
     * Called when payment is confirmed
     * 
     * @param Payment $payment
     * @return bool
     */
    public function handlePaymentConfirmation(Payment $payment): bool
    {
        $payment = $payment->fresh(['job']);

        if (!$payment->job) {
            return false;
        }

        // Verify payment is actually completed
        if ($payment->status !== 'completed') {
            return false;
        }

        // Check and close job
        return $this->checkAndCloseJob($payment->job);
    }
}
