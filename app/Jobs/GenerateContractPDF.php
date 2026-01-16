<?php

namespace App\Jobs;

use App\Models\Quote;
use App\Services\PdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateContractPDF implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Quote $quote
    ) {}

    public function handle(PdfService $pdfService): void
    {
        $pdfPath = $pdfService->generateContract($this->quote);
        
        $this->quote->update([
            'contract_pdf_path' => $pdfPath,
        ]);
    }
}
