<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Models\Job;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class ProcessPaymentTimeout implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $timeoutHours = Setting::get('awaiting_payment_timeout_hours', 24);
        $timeoutDate = now()->subHours($timeoutHours);

        // Find payments that are pending and past timeout
        $pendingPayments = Payment::where('status', 'pending')
            ->where('created_at', '<=', $timeoutDate)
            ->with('job')
            ->get();

        foreach ($pendingPayments as $payment) {
            // Cancel the payment
            $payment->update([
                'status' => 'cancelled',
            ]);

            // Update job status if needed
            if ($payment->job) {
                $job = $payment->job;
                
                // Check if there are other completed payments for this job
                $hasCompletedPayment = Payment::where('job_id', $job->id)
                    ->where('status', 'completed')
                    ->exists();

                if (!$hasCompletedPayment) {
                    // No other payment exists, mark job as awaiting payment
                    $job->update([
                        'status' => 'awaiting_payment',
                    ]);
                }
            }

            // Notify customer about payment timeout
            // TODO: Add notification when notification service is implemented
        }
    }
}
