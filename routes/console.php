<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Ticket;
use App\Models\Job;
use App\Services\Workflow\TriageService;
use App\Services\Workflow\JobOfferService;
use App\Models\Technician;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    // Check for expired triage deadlines
    $expiredTickets = Ticket::where('status', 'pending_triage')
        ->where('triage_deadline_at', '<=', now())
        ->get();

    $triageService = app(TriageService::class);
    foreach ($expiredTickets as $ticket) {
        $triageService->handleTriageTimeout($ticket->id);
    }
})->everyMinute();

Schedule::call(function () {
    // Check for expired job offers
    $expiredJobs = Job::where('status', 'offered')
        ->where('offer_deadline_at', '<=', now())
        ->get();

    $jobOfferService = app(JobOfferService::class);
    foreach ($expiredJobs as $job) {
        $jobOfferService->handleOfferTimeout($job->id);
    }
})->everyMinute();

Schedule::call(function () {
    // Update technician locations (if GPS enabled)
    // This would typically be called from mobile app, but we can clean up stale locations
    Technician::where('last_location_update', '<', now()->subHours(1))
        ->where('status', 'on_duty')
        ->update([
            'latitude' => null,
            'longitude' => null,
        ]);
})->everyFiveMinutes();

Schedule::call(function () {
    // Calculate technician revenue totals (daily summary)
    Technician::chunk(100, function ($technicians) {
        foreach ($technicians as $technician) {
            $revenue = $technician->jobs()
                ->whereHas('payments', function ($query) {
                    $query->where('status', 'completed')
                        ->whereDate('created_at', today());
                })
                ->with('payments')
                ->get()
                ->sum(function ($job) {
                    return $job->payments->where('status', 'completed')->sum('amount');
                });

            // Update cached total (this is a simplified version)
            // In production, you might want to track this differently
        }
    });
})->daily();

Schedule::call(function () {
    // Process payment timeouts (awaiting payment timeout)
    \App\Jobs\ProcessPaymentTimeout::dispatch();
})->hourly();

Schedule::call(function () {
    // Schedule payment reminders for outstanding payments
    \App\Jobs\SchedulePaymentRemindersJob::dispatch();
})->dailyAt('09:00');
