<?php

namespace App\Services\Workflow;

use App\Models\Job;
use App\Models\Payment;

class ReleaseService
{
    public function releaseTechnician(Job $job): void
    {
        if ($job->isReleased()) {
            return;
        }

        // Mark job as released
        $job->update([
            'released_at' => now(),
        ]);

        // Decrement technician's active jobs count
        $job->technician->decrementActiveJobs();

        // Update technician revenue if payment exists
        $payment = $job->payments()->where('status', 'completed')->first();
        if ($payment) {
            $commission = $payment->amount * ($job->technician->commission_rate / 100);
            $job->technician->increment('total_revenue', $commission);
        }

        // Update ticket status
        $job->ticket->update([
            'status' => 'completed',
        ]);

        // Technician is now available for new assignments
    }
}
