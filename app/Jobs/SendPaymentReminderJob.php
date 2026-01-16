<?php

namespace App\Jobs;

use App\Models\Job;
use App\Models\Payment;
use App\Services\Notification\MultiChannelNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPaymentReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Job $job,
        public string $reminderType // 'before_service', 'service_complete', '24_hours', '48_hours', '7_days'
    ) {}

    public function handle(): void
    {
        $job = $this->job->fresh(['ticket.customer', 'payments', 'quote']);
        
        if (!$job->ticket || !$job->ticket->customer) {
            Log::warning('Payment reminder job skipped: no customer', ['job_id' => $job->id]);
            return;
        }

        $customer = $job->ticket->customer;
        
        // Check if payment already completed
        $totalPaid = $job->payments()->where('status', 'completed')->sum('amount');
        $totalDue = $job->quote ? $job->quote->total_amount : 0;
        
        if ($totalPaid >= $totalDue && $totalDue > 0) {
            Log::info('Payment reminder skipped: payment already completed', ['job_id' => $job->id]);
            return;
        }

        $remainingAmount = max(0, $totalDue - $totalPaid);
        
        $notificationService = app(MultiChannelNotificationService::class);
        
        $data = [
            'job_id' => $job->id,
            'ticket_id' => $job->ticket_id,
            'amount' => number_format($remainingAmount, 2),
            'total_amount' => number_format($totalDue, 2),
            'paid_amount' => number_format($totalPaid, 2),
            'payment_url' => url("/customer/tracking?ticket={$job->ticket_id}"),
        ];

        $message = $this->getReminderMessage($this->reminderType, $remainingAmount);
        $title = $this->getReminderTitle($this->reminderType);

        $notificationService->send(
            $customer,
            'payment_reminder',
            $title,
            $message,
            $data
        );
    }

    protected function getReminderTitle(string $type): string
    {
        return match($type) {
            'before_service' => 'Payment Reminder - Service Scheduled',
            'service_complete' => 'Payment Due - Service Complete',
            '24_hours' => 'Payment Reminder - 24 Hours',
            '48_hours' => 'Payment Reminder - 48 Hours',
            '7_days' => 'Final Payment Notice',
            default => 'Payment Reminder',
        };
    }

    protected function getReminderMessage(string $type, float $amount): string
    {
        $amountText = number_format($amount, 2);
        
        return match($type) {
            'before_service' => "Your service is scheduled. Please prepare payment of {$amountText}.",
            'service_complete' => "Your service is complete. Payment of {$amountText} is due.",
            '24_hours' => "This is a friendly reminder that payment of {$amountText} is due.",
            '48_hours' => "Payment of {$amountText} is still pending. Please complete payment soon.",
            '7_days' => "Final notice: Payment of {$amountText} is overdue. Please contact us immediately.",
            default => "Payment reminder: {$amountText} is due.",
        };
    }
}
