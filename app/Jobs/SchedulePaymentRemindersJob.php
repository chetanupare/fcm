<?php

namespace App\Jobs;

use App\Models\Job;
use App\Jobs\SendPaymentReminderJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class SchedulePaymentRemindersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Find jobs that need payment reminders
        $jobs = Job::whereIn('status', ['waiting_payment', 'completed'])
            ->whereNull('released_at')
            ->with(['ticket.customer', 'payments', 'quote'])
            ->get();

        foreach ($jobs as $job) {
            $this->scheduleRemindersForJob($job);
        }
    }

    protected function scheduleRemindersForJob(Job $job): void
    {
        $totalDue = $job->quote ? $job->quote->total_amount : 0;
        $totalPaid = $job->payments()->where('status', 'completed')->sum('amount');
        
        if ($totalPaid >= $totalDue || $totalDue <= 0) {
            return; // Payment complete or no amount due
        }

        $completedAt = $job->payment_received_at ?? $job->updated_at;
        $hoursSinceCompletion = now()->diffInHours($completedAt);
        
        // Schedule reminders based on time since completion
        if ($hoursSinceCompletion >= 24 && $hoursSinceCompletion < 48) {
            // Check if 24-hour reminder already sent
            if (!$this->reminderSent($job, '24_hours')) {
                SendPaymentReminderJob::dispatch($job, '24_hours')
                    ->delay(now()->addMinutes(5));
            }
        } elseif ($hoursSinceCompletion >= 48 && $hoursSinceCompletion < 168) {
            // Check if 48-hour reminder already sent
            if (!$this->reminderSent($job, '48_hours')) {
                SendPaymentReminderJob::dispatch($job, '48_hours')
                    ->delay(now()->addMinutes(5));
            }
        } elseif ($hoursSinceCompletion >= 168) {
            // 7 days - final notice
            if (!$this->reminderSent($job, '7_days')) {
                SendPaymentReminderJob::dispatch($job, '7_days')
                    ->delay(now()->addMinutes(5));
            }
        }
    }

    protected function reminderSent(Job $job, string $type): bool
    {
        // Check notification history for this reminder type
        // This is a simplified check - in production, track reminder history
        return false;
    }
}
