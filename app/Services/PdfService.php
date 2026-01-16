<?php

namespace App\Services;

use App\Models\Quote;
use Dompdf\Dompdf;
use Dompdf\Options;

class PdfService
{
    public function generateContract(Quote $quote): string
    {
        // Check if invoice generation is enabled
        $invoiceEnabled = \App\Models\Setting::get('invoice_generation', true);
        if (!$invoiceEnabled) {
            throw new \Exception('Invoice generation is disabled');
        }

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new Dompdf($options);

        $template = \App\Models\Setting::get('invoice_template', 'default');
        $html = $this->generateContractHtml($quote, $template);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = "contracts/quote_{$quote->id}_" . time() . '.pdf';
        $path = storage_path('app/public/' . $filename);
        
        // Ensure directory exists
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($path, $dompdf->output());

        return $filename;
    }

    protected function generateContractHtml(Quote $quote, string $template = 'default'): string
    {
        $job = $quote->job;
        $ticket = $job->ticket;
        $customer = $ticket->customer;
        $device = $ticket->device;
        
        // Get company information from settings
        $companyName = \App\Models\Setting::get('company_name', 'Repair Service');
        $companyAddress = \App\Models\Setting::get('company_address', '');
        $companyWebsite = \App\Models\Setting::get('company_website', '');
        $supportEmail = \App\Models\Setting::get('support_email', '');
        $supportPhone = \App\Models\Setting::get('support_phone', '');
        
        // Get currency helper
        $currencySymbol = \App\Helpers\CurrencyHelper::symbol();
        $currencyAlignment = \App\Helpers\CurrencyHelper::alignment();
        
        $formatCurrency = function($amount) use ($currencySymbol, $currencyAlignment) {
            $formatted = number_format($amount, 2);
            return $currencyAlignment === 'left' ? $currencySymbol . $formatted : $formatted . $currencySymbol;
        };

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Repair Contract</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
                .company-info { text-align: center; margin-bottom: 20px; font-size: 12px; color: #666; }
                .section { margin-bottom: 20px; }
                .items-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                .items-table th, .items-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .items-table th { background-color: #f2f2f2; }
                .total { text-align: right; font-weight: bold; margin-top: 20px; }
                .signature { margin-top: 50px; }
            </style>
        </head>
        <body>
            <div class="company-info">
                <h2>' . htmlspecialchars($companyName) . '</h2>
                ' . ($companyAddress ? '<p>' . htmlspecialchars($companyAddress) . '</p>' : '') . '
                ' . ($companyWebsite ? '<p>' . htmlspecialchars($companyWebsite) . '</p>' : '') . '
                ' . ($supportEmail ? '<p>Email: ' . htmlspecialchars($supportEmail) . '</p>' : '') . '
                ' . ($supportPhone ? '<p>Phone: ' . htmlspecialchars($supportPhone) . '</p>' : '') . '
            </div>
            <div class="header">
                <h1>Repair Service Contract</h1>
                <p>Quote #' . $quote->id . '</p>
            </div>

            <div class="section">
                <h3>Customer Information</h3>
                <p><strong>Name:</strong> ' . htmlspecialchars($customer->name) . '</p>
                <p><strong>Email:</strong> ' . htmlspecialchars($customer->email) . '</p>
                <p><strong>Phone:</strong> ' . htmlspecialchars($customer->phone ?? 'N/A') . '</p>
            </div>

            <div class="section">
                <h3>Device Information</h3>
                <p><strong>Type:</strong> ' . htmlspecialchars($device->device_type) . '</p>
                <p><strong>Brand:</strong> ' . htmlspecialchars($device->brand) . '</p>
                <p><strong>Model:</strong> ' . htmlspecialchars($device->model ?? 'N/A') . '</p>
                <p><strong>Issue:</strong> ' . htmlspecialchars($ticket->issue_description) . '</p>
            </div>

            <div class="section">
                <h3>Services & Pricing</h3>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>';

        foreach ($quote->items as $item) {
            $html .= '
                        <tr>
                            <td>' . htmlspecialchars($item['service_name']) . '</td>
                            <td>' . $item['quantity'] . '</td>
                            <td>' . $formatCurrency($item['price']) . '</td>
                            <td>' . $formatCurrency($item['total']) . '</td>
                        </tr>';
        }

        $html .= '
                    </tbody>
                </table>
                <div class="total">
                    <p>Subtotal: ' . $formatCurrency($quote->subtotal) . '</p>
                    <p>Tax: ' . $formatCurrency($quote->tax) . '</p>
                    <p><strong>Total: ' . $formatCurrency($quote->total) . '</strong></p>
                </div>
            </div>

            <div class="signature">
                <p><strong>Customer Signature:</strong></p>
                <p>Date: ' . $quote->signed_at->format('Y-m-d H:i:s') . '</p>
            </div>

            <div style="margin-top: 50px; font-size: 10px; color: #666;">
                <p>This contract is generated automatically. By signing, the customer agrees to the terms and pricing listed above.</p>
            </div>
        </body>
        </html>';

        return $html;
    }
}
