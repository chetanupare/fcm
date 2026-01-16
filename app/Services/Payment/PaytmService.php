<?php

namespace App\Services\Payment;

use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Paytm\Paytm;
use Paytm\Paytm\PaytmOrder;

class PaytmService
{
    protected string $merchantId;
    protected string $merchantKey;
    protected string $website;
    protected string $industryType;
    protected string $channelId;
    protected bool $sandbox;

    public function __construct()
    {
        $this->merchantId = config('services.paytm.merchant_id');
        $this->merchantKey = config('services.paytm.merchant_key');
        $this->website = config('services.paytm.website', 'WEBSTAGING');
        $this->industryType = config('services.paytm.industry_type', 'Retail');
        $this->channelId = config('services.paytm.channel_id', 'WEB');
        $this->sandbox = config('services.paytm.sandbox', true);
    }

    public function createPayment(Payment $payment): array
    {
        $orderId = 'ORDER_' . $payment->id . '_' . time();
        $amount = $payment->amount;
        $customerId = (string) $payment->job->ticket->customer_id;

        $paytmOrder = new PaytmOrder([
            'ORDER_ID' => $orderId,
            'CUST_ID' => $customerId,
            'TXN_AMOUNT' => (string) $amount,
            'CHANNEL_ID' => $this->channelId,
            'INDUSTRY_TYPE_ID' => $this->industryType,
            'WEBSITE' => $this->website,
            'CALLBACK_URL' => config('app.url') . '/api/webhooks/paytm',
        ]);

        $paytm = new Paytm($this->merchantId, $this->merchantKey, $this->sandbox);
        
        try {
            $response = $paytm->initiateTransaction($paytmOrder);

            $payment->update([
                'transaction_id' => $orderId,
                'gateway_response' => $response,
            ]);

            return [
                'order_id' => $orderId,
                'txn_token' => $response['body']['txnToken'] ?? null,
                'redirect_url' => $this->sandbox 
                    ? 'https://securegw-stage.paytm.in/theia/api/v1/showPaymentPage'
                    : 'https://securegw.paytm.in/theia/api/v1/showPaymentPage',
            ];
        } catch (\Exception $e) {
            Log::error('Paytm payment creation failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function verifyWebhook(array $payload): bool
    {
        $orderId = $payload['ORDERID'] ?? null;
        if (!$orderId) {
            return false;
        }

        $paytm = new Paytm($this->merchantId, $this->merchantKey, $this->sandbox);
        
        try {
            $response = $paytm->verifyTransaction($orderId);
            return isset($response['STATUS']) && $response['STATUS'] === 'TXN_SUCCESS';
        } catch (\Exception $e) {
            Log::error('Paytm webhook verification failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
