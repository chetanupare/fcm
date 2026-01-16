<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Job;
use App\Services\Payment\RazorpayService;
use App\Services\Payment\PhonePeService;
use App\Services\Payment\PaytmService;
use Illuminate\Http\Request;

/**
 * @tags Technician
 * 
 * Payment initiation for online gateways
 */
class PaymentController extends Controller
{
    public function initiatePayment(Request $request, int $jobId)
    {
        $request->validate([
            'method' => 'required|in:razorpay,phonepe,paytm',
            'tip_amount' => 'nullable|numeric|min:0',
        ]);

        // Check if payment gateway is enabled
        $method = $request->method;
        $enabled = \App\Models\Setting::get("{$method}_enabled", false);
        
        if (!$enabled) {
            return response()->json([
                'message' => ucfirst($method) . ' payment gateway is not enabled',
            ], 403);
        }

        $job = Job::where('technician_id', $request->user()->technician->id)
            ->with('quote')
            ->findOrFail($jobId);

        if (!$job->quote || !$job->quote->isSigned()) {
            return response()->json([
                'message' => 'Contract must be signed before initiating payment',
            ], 422);
        }

        // Check if payment already exists
        $existingPayment = Payment::where('job_id', $jobId)
            ->whereIn('method', ['razorpay', 'phonepe', 'paytm'])
            ->where('status', 'pending')
            ->first();

        if ($existingPayment) {
            return response()->json([
                'message' => 'Payment already initiated',
                'payment' => $existingPayment,
            ], 422);
        }

        // Check if tips are enabled
        $tipsEnabled = \App\Models\Setting::get('enable_service_tips', false);
        $tipAmount = 0;
        if ($tipsEnabled && $request->has('tip_amount') && $request->tip_amount > 0) {
            $tipAmount = $request->tip_amount;
        }

        // Create payment record
        $payment = Payment::create([
            'job_id' => $job->id,
            'quote_id' => $job->quote_id,
            'amount' => $job->quote->total,
            'tip_amount' => $tipAmount,
            'currency' => $request->user()->currency_preference ?? 'USD',
            'method' => $request->method,
            'status' => 'pending',
        ]);

        try {
            $gatewayData = match ($request->method) {
                'razorpay' => app(RazorpayService::class)->createOrder($payment),
                'phonepe' => app(PhonePeService::class)->createPayment($payment),
                'paytm' => app(PaytmService::class)->createPayment($payment),
            };

            return response()->json([
                'payment' => $payment,
                'gateway_data' => $gatewayData,
            ]);
        } catch (\Exception $e) {
            $payment->update(['status' => 'failed']);
            return response()->json([
                'message' => 'Payment initiation failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
