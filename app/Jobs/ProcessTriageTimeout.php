<?php

namespace App\Jobs;

use App\Services\Workflow\TriageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessTriageTimeout implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $ticketId
    ) {}

    public function handle(TriageService $triageService): void
    {
        $triageService->handleTriageTimeout($this->ticketId);
    }
}
