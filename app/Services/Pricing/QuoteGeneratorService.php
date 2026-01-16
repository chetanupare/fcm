<?php

namespace App\Services\Pricing;

use App\Models\Job;
use App\Models\Quote;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

class QuoteGeneratorService
{
    public function generateQuote(Job $job, array $serviceItems): Quote
    {
        // Validate all services exist and get prices from catalog
        $items = [];
        $subtotal = 0;

        foreach ($serviceItems as $item) {
            $service = Service::findOrFail($item['service_id']);
            
            if (!$service->is_active) {
                throw new \Exception("Service {$service->id} is not active");
            }

            $quantity = $item['quantity'] ?? 1;
            $price = $service->price; // Always use catalog price
            $itemTotal = $price * $quantity;

            $items[] = [
                'service_id' => $service->id,
                'service_name' => $service->name,
                'quantity' => $quantity,
                'price' => $price,
                'total' => $itemTotal,
            ];

            $subtotal += $itemTotal;
        }

        // Calculate tax (if applicable)
        $taxRate = \App\Models\Setting::get('tax_rate', 0);
        $tax = $subtotal * ($taxRate / 100);
        $total = $subtotal + $tax;

        // Create quote
        $quote = Quote::create([
            'job_id' => $job->id,
            'items' => $items,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'status' => 'pending',
        ]);

        // Update job with quote
        $job->update([
            'quote_id' => $quote->id,
        ]);

        return $quote;
    }

    public function signContract(Quote $quote, string $signatureData): void
    {
        // Save signature
        $signaturePath = $this->saveSignature($quote, $signatureData);

        $quote->update([
            'customer_signature' => $signaturePath,
            'status' => 'signed',
            'signed_at' => now(),
        ]);

        $quote->job->update([
            'contract_signed_at' => now(),
        ]);

        // Generate PDF contract
        $pdfService = app(\App\Services\PdfService::class);
        $pdfPath = $pdfService->generateContract($quote);
        
        $quote->update([
            'contract_pdf_path' => $pdfPath,
        ]);
    }

    protected function saveSignature(Quote $quote, string $signatureData): string
    {
        // Decode base64 signature if needed
        if (str_starts_with($signatureData, 'data:image')) {
            [$header, $data] = explode(',', $signatureData, 2);
            $signatureData = base64_decode($data);
        }

        $filename = "signatures/quote_{$quote->id}_" . time() . '.png';
        $path = storage_path('app/public/' . $filename);
        
        // Ensure directory exists
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($path, $signatureData);

        return $filename;
    }
}
