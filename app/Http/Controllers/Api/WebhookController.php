<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @tags Misc
 * 
 * Payment gateway webhooks
 */
class WebhookController extends Controller
{
    public function stripe(Request $request)
    {
        // TODO: Implement Stripe webhook verification and processing
        // This is a placeholder - implement proper webhook signature verification
        
        $payload = $request->all();
        Log::info('Stripe webhook received', $payload);

        // Process payment status update
        if (isset($payload['data']['object']['id'])) {
            $transactionId = $payload['data']['object']['id'];
            $status = $payload['data']['object']['status'] ?? 'pending';

            $payment = Payment::where('transaction_id', $transactionId)->first();
            
            if ($payment) {
                $payment->update([
                    'status' => $status === 'succeeded' ? 'completed' : 'failed',
                    'gateway_response' => $payload,
                ]);

                if ($status === 'succeeded') {
                    $payment->update(['status' => 'completed']);
                    
                    // Automatic job closure
                    $closureService = app(\App\Services\Workflow\AutomaticJobClosureService::class);
                    $closureService->handlePaymentConfirmation($payment->fresh());
                }
            }
        }

        return response()->json(['received' => true]);
    }

    public function paypal(Request $request)
    {
        // TODO: Implement PayPal webhook verification and processing
        $payload = $request->all();
        Log::info('PayPal webhook received', $payload);

        // Similar processing as Stripe
        return response()->json(['received' => true]);
    }

    public function razorpay(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('X-Razorpay-Signature');

        $razorpayService = app(\App\Services\Payment\RazorpayService::class);

        if (!$razorpayService->verifyWebhook($payload, $signature)) {
            Log::warning('Razorpay webhook verification failed', $payload);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = $payload['event'] ?? null;
        $paymentData = $payload['payload']['payment']['entity'] ?? null;

        if ($event === 'payment.captured' && $paymentData) {
            $transactionId = $paymentData['order_id'] ?? null;
            $payment = Payment::where('transaction_id', $transactionId)->first();

            if ($payment) {
                $payment->update([
                    'status' => 'completed',
                    'gateway_response' => $payload,
                ]);

                $payment->update(['status' => 'completed']);
                
                // Automatic job closure
                $closureService = app(\App\Services\Workflow\AutomaticJobClosureService::class);
                $closureService->handlePaymentConfirmation($payment->fresh());
            }
        }

        return response()->json(['received' => true]);
    }

    public function phonepe(Request $request)
    {
        $payload = $request->all();
        $phonepeService = app(\App\Services\Payment\PhonePeService::class);

        if (!$phonepeService->verifyWebhook($payload)) {
            Log::warning('PhonePe webhook verification failed', $payload);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $responseData = json_decode(base64_decode($payload['response'] ?? ''), true);
        $transactionId = $responseData['data']['merchantTransactionId'] ?? null;
        $code = $responseData['code'] ?? null;

        if ($code === 'PAYMENT_SUCCESS' && $transactionId) {
            $payment = Payment::where('transaction_id', $transactionId)->first();

            if ($payment) {
                $payment->update([
                    'status' => 'completed',
                    'gateway_response' => $responseData,
                ]);

                $payment->update(['status' => 'completed']);
                
                // Automatic job closure
                $closureService = app(\App\Services\Workflow\AutomaticJobClosureService::class);
                $closureService->handlePaymentConfirmation($payment->fresh());
            }
        }

        return response()->json(['received' => true]);
    }

    public function paytm(Request $request)
    {
        $payload = $request->all();
        $paytmService = app(\App\Services\Payment\PaytmService::class);

        if (!$paytmService->verifyWebhook($payload)) {
            Log::warning('Paytm webhook verification failed', $payload);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $orderId = $payload['ORDERID'] ?? null;
        $status = $payload['STATUS'] ?? null;

        if ($status === 'TXN_SUCCESS' && $orderId) {
            $payment = Payment::where('transaction_id', $orderId)->first();

            if ($payment) {
                $payment->update([
                    'status' => 'completed',
                    'gateway_response' => $payload,
                ]);

                $payment->update(['status' => 'completed']);
                
                // Automatic job closure
                $closureService = app(\App\Services\Workflow\AutomaticJobClosureService::class);
                $closureService->handlePaymentConfirmation($payment->fresh());
            }
        }

        return response()->json(['received' => true]);
    }
}
