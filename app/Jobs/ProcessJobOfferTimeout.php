<?php

namespace App\Jobs;

use App\Models\Job;
use App\Services\Workflow\JobOfferService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessJobOfferTimeout implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $jobId
    ) {}

    public function handle(JobOfferService $jobOfferService): void
    {
        $jobOfferService->handleOfferTimeout($this->jobId);
    }
}
