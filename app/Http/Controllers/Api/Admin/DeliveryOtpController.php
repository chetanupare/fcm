<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryOtp;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class DeliveryOtpController extends Controller
{
    public function generate(Request $request, $jobId)
    {
        $job = Job::with('ticket')->findOrFail($jobId);

        $validated = $request->validate([
            'type' => 'required|in:delivery,pickup,verification',
            'expires_in_minutes' => 'nullable|integer|min:5|max:1440',
        ]);

        // Generate 6-digit OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        $expiresAt = now()->addMinutes($validated['expires_in_minutes'] ?? 60);

        $deliveryOtp = DeliveryOtp::create([
            'job_id' => $jobId,
            'customer_id' => $job->ticket->customer_id,
            'otp' => $otp,
            'type' => $validated['type'],
            'status' => 'pending',
            'expires_at' => $expiresAt,
        ]);

        // TODO: Send OTP via SMS/Email to customer
        // Notification::send($job->ticket->customer, new OtpNotification($otp));

        return response()->json([
            'otp' => $otp,
            'expires_at' => $expiresAt,
            'delivery_otp' => $deliveryOtp,
        ], 201);
    }

    public function verify(Request $request)
    {
        $validated = $request->validate([
            'otp' => 'required|string|size:6',
            'job_id' => 'required|exists:service_jobs,id',
            'verified_by' => 'nullable|string|max:255',
        ]);

        $deliveryOtp = DeliveryOtp::where('job_id', $validated['job_id'])
            ->where('otp', $validated['otp'])
            ->where('status', 'pending')
            ->first();

        if (!$deliveryOtp) {
            return response()->json(['message' => 'Invalid OTP'], 422);
        }

        if ($deliveryOtp->isExpired()) {
            $deliveryOtp->update(['status' => 'expired']);
            return response()->json(['message' => 'OTP has expired'], 422);
        }

        $deliveryOtp->update([
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => $validated['verified_by'] ?? 'System',
            'verification_method' => 'manual',
        ]);

        return response()->json([
            'message' => 'OTP verified successfully',
            'delivery_otp' => $deliveryOtp,
        ]);
    }

    public function getByJob($jobId)
    {
        $otps = DeliveryOtp::where('job_id', $jobId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($otps);
    }
}
