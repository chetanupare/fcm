<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SchedulePaymentRemindersJob;

class SchedulePaymentReminders extends Command
{
    protected $signature = 'payments:schedule-reminders';
    protected $description = 'Schedule payment reminder jobs for pending payments';

    public function handle()
    {
        $this->info('Scheduling payment reminders...');
        
        SchedulePaymentRemindersJob::dispatch();
        
        $this->info('Payment reminders scheduled successfully.');
    }
}
