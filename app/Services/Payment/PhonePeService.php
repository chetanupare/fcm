<?php

namespace App\Services\Payment;

use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PhonePeService
{
    protected string $merchantId;
    protected string $saltKey;
    protected string $saltIndex;
    protected bool $sandbox;

    public function __construct()
    {
        $this->merchantId = config('services.phonepe.merchant_id');
        $this->saltKey = config('services.phonepe.salt_key');
        $this->saltIndex = config('services.phonepe.salt_index', '1');
        $this->sandbox = config('services.phonepe.sandbox', true);
    }

    public function createPayment(Payment $payment): array
    {
        $baseUrl = $this->sandbox 
            ? 'https://api-preprod.phonepe.com/apis/pg-sandbox'
            : 'https://api.phonepe.com/apis/pg-sandbox';

        $transactionId = 'TXN_' . $payment->id . '_' . time();
        $amount = $payment->amount * 100; // Convert to paise

        $payload = [
            'merchantId' => $this->merchantId,
            'merchantTransactionId' => $transactionId,
            'merchantUserId' => (string) $payment->job->ticket->customer_id,
            'amount' => $amount,
            'redirectUrl' => config('app.url') . '/api/webhooks/phonepe',
            'redirectMode' => 'REDIRECT',
            'callbackUrl' => config('app.url') . '/api/webhooks/phonepe',
            'mobileNumber' => $payment->job->ticket->customer->phone ?? '',
            'paymentInstrument' => [
                'type' => 'PAY_PAGE',
            ],
        ];

        $base64Payload = base64_encode(json_encode($payload));
        $xVerify = hash('sha256', $base64Payload . '/pg/v1/pay' . $this->saltKey) . '###' . $this->saltIndex;

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-VERIFY' => $xVerify,
        ])->post($baseUrl . '/pg/v1/pay', [
            'request' => $base64Payload,
        ]);

        if ($response->successful()) {
            $responseData = $response->json();
            
            $payment->update([
                'transaction_id' => $transactionId,
                'gateway_response' => $responseData,
            ]);

            return [
                'transaction_id' => $transactionId,
                'redirect_url' => $responseData['data']['instrumentResponse']['redirectInfo']['url'] ?? null,
            ];
        }

        Log::error('PhonePe payment creation failed', [
            'payment_id' => $payment->id,
            'response' => $response->body(),
        ]);

        throw new \Exception('PhonePe payment creation failed');
    }

    public function verifyWebhook(array $payload): bool
    {
        $xVerify = request()->header('X-VERIFY');
        $xVerifyIndex = request()->header('X-VERIFY-INDEX');

        if (!$xVerify || !$xVerifyIndex) {
            return false;
        }

        $base64Payload = $payload['response'] ?? '';
        $calculatedHash = hash('sha256', $base64Payload . $this->saltKey) . '###' . $this->saltIndex;

        return hash_equals($calculatedHash, $xVerify);
    }
}
