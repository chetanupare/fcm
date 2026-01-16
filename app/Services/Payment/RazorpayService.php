<?php

namespace App\Services\Payment;

use Razorpay\Api\Api;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class RazorpayService
{
    protected Api $api;

    public function __construct()
    {
        $keyId = config('services.razorpay.key_id');
        $keySecret = config('services.razorpay.key_secret');
        
        $this->api = new Api($keyId, $keySecret);
    }

    public function createOrder(Payment $payment): array
    {
        try {
            $orderData = [
                'receipt' => 'order_' . $payment->id,
                'amount' => $payment->amount * 100, // Convert to paise
                'currency' => strtoupper($payment->currency),
                'notes' => [
                    'payment_id' => $payment->id,
                    'job_id' => $payment->job_id,
                ],
            ];

            $order = $this->api->order->create($orderData);

            $payment->update([
                'transaction_id' => $order->id,
                'gateway_response' => $order->toArray(),
            ]);

            return [
                'order_id' => $order->id,
                'amount' => $order->amount,
                'currency' => $order->currency,
                'key_id' => config('services.razorpay.key_id'),
                'callback_url' => config('app.url') . '/api/webhooks/razorpay',
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay order creation failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function verifyWebhook(array $payload, string $signature): bool
    {
        $webhookSecret = config('services.razorpay.webhook_secret');
        
        try {
            $this->api->utility->verifyWebhookSignature(
                json_encode($payload),
                $signature,
                $webhookSecret
            );
            return true;
        } catch (\Exception $e) {
            Log::error('Razorpay webhook verification failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function verifyPayment(string $paymentId): bool
    {
        try {
            $payment = $this->api->payment->fetch($paymentId);
            return $payment->status === 'captured' || $payment->status === 'authorized';
        } catch (\Exception $e) {
            Log::error('Razorpay payment verification failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
