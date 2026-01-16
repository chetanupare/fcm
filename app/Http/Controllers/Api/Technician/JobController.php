<?php

namespace App\Http\Controllers\Api\Technician;

use App\Http\Controllers\Controller;
use App\Http\Requests\Technician\GenerateQuoteRequest;
use App\Models\Job;
use App\Models\Service;
use App\Services\Workflow\JobOfferService;
use App\Services\Pricing\QuoteGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

/**
 * @tags Technician
 * 
 * Job management and execution
 */
class JobController extends Controller
{
    protected JobOfferService $jobOfferService;
    protected QuoteGeneratorService $quoteGeneratorService;

    public function __construct(
        JobOfferService $jobOfferService,
        QuoteGeneratorService $quoteGeneratorService
    ) {
        $this->jobOfferService = $jobOfferService;
        $this->quoteGeneratorService = $quoteGeneratorService;
    }

    public function offered(Request $request)
    {
        $technician = $request->user()->technician;
        
        $jobs = Job::where('technician_id', $technician->id)
            ->where('status', 'offered')
            ->with(['ticket.device', 'ticket.customer'])
            ->get()
            ->map(function ($job) {
                return [
                    'id' => $job->id,
                    'ticket_id' => $job->ticket_id,
                    'device' => $job->ticket->device->brand . ' ' . $job->ticket->device->device_type,
                    'issue' => $job->ticket->issue_description,
                    'deadline' => $job->offer_deadline_at,
                    'countdown' => $job->offer_deadline_at 
                        ? max(0, $job->offer_deadline_at->diffInSeconds(now()))
                        : 0,
                ];
            });

        return response()->json([
            'jobs' => $jobs,
        ]);
    }

    public function assigned(Request $request)
    {
        $technician = $request->user()->technician;
        
        $jobs = Job::where('technician_id', $technician->id)
            ->whereNotIn('status', ['offered', 'cancelled', 'completed'])
            ->with(['ticket.device', 'ticket.customer'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($job) {
                return [
                    'id' => $job->id,
                    'ticket_id' => $job->ticket_id,
                    'device' => $job->ticket->device->brand . ' ' . $job->ticket->device->device_type,
                    'issue' => $job->ticket->issue_description,
                    'status' => $job->status,
                    'address' => $job->ticket->address,
                    'preferred_date' => $job->ticket->preferred_date,
                    'preferred_time' => $job->ticket->preferred_time,
                    'customer' => [
                        'name' => $job->ticket->customer->name,
                        'phone' => $job->ticket->customer->phone,
                        'email' => $job->ticket->customer->email,
                    ],
                    'created_at' => $job->created_at->toIso8601String(),
                    'updated_at' => $job->updated_at->toIso8601String(),
                ];
            });

        return response()->json([
            'jobs' => $jobs,
        ]);
    }

    public function accept(Request $request, int $id)
    {
        $job = Job::where('technician_id', $request->user()->technician->id)
            ->findOrFail($id);

        $accepted = $this->jobOfferService->accept($job);

        if (!$accepted) {
            return response()->json([
                'message' => 'Cannot accept this job offer',
            ], 422);
        }

        // Return job with customer details (now revealed)
        $job->load(['ticket.customer', 'ticket.device']);

        return response()->json([
            'job' => $job,
            'customer' => [
                'name' => $job->ticket->customer->name,
                'phone' => $job->ticket->customer->phone,
                'email' => $job->ticket->customer->email,
            ],
        ]);
    }

    public function reject(Request $request, int $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $job = Job::where('technician_id', $request->user()->technician->id)
            ->findOrFail($id);

        $this->jobOfferService->reject($job, $request->reason);

        return response()->json([
            'message' => 'Job offer rejected',
        ]);
    }

    public function show(Request $request, int $id)
    {
        $job = Job::where('technician_id', $request->user()->technician->id)
            ->with(['ticket.device', 'ticket.customer', 'quote', 'checklists.checklist'])
            ->findOrFail($id);

        return response()->json([
            'job' => $job,
        ]);
    }

    /**
     * Generate a quote for a job
     */
    public function generateQuote(GenerateQuoteRequest $request, int $id)
    {

        $job = Job::where('technician_id', $request->user()->technician->id)
            ->findOrFail($id);

        if ($job->quote_id) {
            return response()->json([
                'message' => 'Quote already exists for this job',
            ], 422);
        }

        try {
            $quote = $this->quoteGeneratorService->generateQuote($job, $request->input('items'));
            
            return response()->json([
                'quote' => $quote,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function signContract(Request $request, int $id)
    {
        $request->validate([
            'signature' => 'required|string',
        ]);

        $job = Job::where('technician_id', $request->user()->technician->id)
            ->with('quote')
            ->findOrFail($id);

        if (!$job->quote) {
            return response()->json([
                'message' => 'Quote not found. Generate quote first.',
            ], 422);
        }

        $this->quoteGeneratorService->signContract($job->quote, $request->signature);

        return response()->json([
            'message' => 'Contract signed successfully',
            'quote' => $job->quote->fresh(),
        ]);
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:accepted,en_route,component_pickup,arrived,diagnosing,quoted,signed_contract,repairing,waiting_parts,quality_check,waiting_payment,completed,released,no_show,cannot_repair',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'notes' => 'nullable|string|max:1000',
        ]);

        $job = Job::where('technician_id', $request->user()->technician->id)
            ->findOrFail($id);

        // Validate state transition
        $validTransitions = $this->getValidTransitions($job->status);
        if (!in_array($request->status, $validTransitions)) {
            return response()->json([
                'message' => "Invalid status transition from {$job->status} to {$request->status}",
            ], 422);
        }

        $updateData = ['status' => $request->status];
        
        if ($request->has('notes')) {
            $updateData['notes'] = $request->notes;
        }

        $job->update($updateData);

        // Update location if provided
        if ($request->has('latitude') && $request->has('longitude')) {
            $technician = $request->user()->technician;
            $technician->update([
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'last_location_update' => now(),
            ]);
        }

        // Handle special statuses
        if ($request->status === 'no_show') {
            $this->handleNoShow($job);
        } elseif ($request->status === 'cannot_repair') {
            $this->handleCannotRepair($job);
        }

        // Send notification to customer about status change
        $customer = $job->ticket->customer;
        if ($customer && in_array($request->status, ['en_route', 'arrived', 'diagnosing', 'repairing', 'quality_check', 'completed'])) {
            $customer->notify(new \App\Notifications\JobStatusNotification(
                $job->fresh(),
                $request->status
            ));
        }

        return response()->json([
            'job' => $job->fresh(),
        ]);
    }

    public function uploadAfterPhoto(Request $request, int $id)
    {
        $request->validate([
            'photo' => 'required|image|max:5120',
        ]);

        $job = Job::where('technician_id', $request->user()->technician->id)
            ->findOrFail($id);

        $imageQuality = \App\Models\Setting::get('upload_image_quality', 85);
        
        // Store photo with quality setting using Intervention Image
        $filename = time() . '_' . uniqid() . '.' . $request->file('photo')->getClientOriginalExtension();
        $path = 'jobs/after_photos/' . $filename;
        $fullPath = storage_path('app/public/' . $path);
        
        // Ensure directory exists
        $directory = dirname($fullPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // Apply quality compression
        $image = Image::read($request->file('photo'));
        $image->save($fullPath, quality: $imageQuality);

        $job->update([
            'after_photo' => $path,
        ]);

        return response()->json([
            'message' => 'Photo uploaded successfully',
            'photo_url' => Storage::url($path),
        ]);
    }

    public function recordPayment(Request $request, int $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'tip_amount' => 'nullable|numeric|min:0',
            'method' => 'required|in:cash,stripe,paypal,cod,razorpay,phonepe,paytm',
            'transaction_id' => 'nullable|string|max:255',
        ]);

        // Check if tips are enabled
        $tipsEnabled = \App\Models\Setting::get('enable_service_tips', false);
        if ($request->has('tip_amount') && $request->tip_amount > 0 && !$tipsEnabled) {
            return response()->json([
                'message' => 'Service tips are not enabled',
            ], 403);
        }

        // Check if payment method is enabled
        $method = $request->method;
        $enabled = \App\Models\Setting::get("{$method}_enabled", $method === 'cash' || $method === 'cod' ? true : false);
        
        if (!$enabled) {
            return response()->json([
                'message' => ucfirst($method) . ' payment method is not enabled',
            ], 403);
        }

        $job = Job::where('technician_id', $request->user()->technician->id)
            ->with('quote')
            ->findOrFail($id);

        // Validate amount matches quote total (with 0.01 tolerance for rounding)
        $quoteTotal = $job->quote->total;
        if (abs($request->amount - $quoteTotal) > 0.01) {
            return response()->json([
                'message' => "Payment amount ({$request->amount}) does not match quote total ({$quoteTotal})",
            ], 422);
        }

        if (!$job->quote || !$job->quote->isSigned()) {
            return response()->json([
                'message' => 'Contract must be signed before recording payment',
            ], 422);
        }

        // Check if all mandatory checklists are completed
        $mandatoryChecklists = $job->checklists()
            ->whereHas('checklist', function ($query) {
                $query->where('is_mandatory', true);
            })
            ->get();

        $incomplete = $mandatoryChecklists->filter(function ($jc) {
            return !$jc->is_completed;
        });

        if ($incomplete->count() > 0) {
            return response()->json([
                'message' => 'All mandatory checklists must be completed before recording payment',
                'incomplete_checklists' => $incomplete->pluck('checklist.name'),
            ], 422);
        }

        // Check if after photo is uploaded
        if (!$job->after_photo) {
            return response()->json([
                'message' => 'After photo must be uploaded before recording payment',
            ], 422);
        }

        $payment = \App\Models\Payment::create([
            'job_id' => $job->id,
            'quote_id' => $job->quote_id,
            'amount' => $request->amount,
            'tip_amount' => $tipsEnabled ? ($request->tip_amount ?? 0) : 0,
            'currency' => $request->user()->currency_preference ?? 'USD',
            'method' => $request->method,
            'transaction_id' => $request->transaction_id,
            'status' => in_array($request->method, ['cash', 'cod']) ? 'completed' : 'pending',
        ]);

        $job->update([
            'payment_received_at' => now(),
            'status' => 'completed',
        ]);

        // Only release technician for cash/cod payments
        // Online payments will release via webhook
        if (in_array($request->method, ['cash', 'cod'])) {
            $releaseService = app(\App\Services\Workflow\ReleaseService::class);
            $releaseService->releaseTechnician($job);
        }

        // Send notification to customer
        $customer = $job->ticket->customer;
        if ($customer) {
            $customer->notify(new \App\Notifications\PaymentReceivedNotification($payment));
        }

        return response()->json([
            'message' => 'Payment recorded successfully',
            'payment' => $payment->load('job', 'quote'),
            'total_amount' => $payment->amount + $payment->tip_amount,
        ]);
    }

    protected function getValidTransitions(string $currentStatus): array
    {
        $transitions = [
            'offered' => ['accepted'],
            'accepted' => ['en_route', 'component_pickup'],
            'en_route' => ['component_pickup', 'arrived'],
            'component_pickup' => ['arrived', 'en_route'],
            'arrived' => ['diagnosing', 'no_show'],
            'diagnosing' => ['quoted', 'waiting_parts', 'repairing', 'cannot_repair'],
            'quoted' => ['signed_contract'],
            'signed_contract' => ['repairing', 'waiting_parts'],
            'waiting_parts' => ['repairing'],
            'repairing' => ['quality_check'],
            'quality_check' => ['waiting_payment', 'completed'],
            'waiting_payment' => ['completed'],
            'completed' => ['released'],
        ];

        return $transitions[$currentStatus] ?? [];
    }

    protected function handleNoShow(Job $job): void
    {
        // Apply visit fee
        $visitFeeService = \App\Models\Service::where('category', 'visit_fee')
            ->where('is_active', true)
            ->first();

        if ($visitFeeService && !$job->quote_id) {
            $this->quoteGeneratorService->generateQuote($job, [
                ['service_id' => $visitFeeService->id, 'quantity' => 1],
            ]);
        }

        $job->update(['status' => 'completed']);
        $releaseService = app(\App\Services\Workflow\ReleaseService::class);
        $releaseService->releaseTechnician($job);
    }

    protected function handleCannotRepair(Job $job): void
    {
        // Apply diagnosis fee only
        $diagnosisService = \App\Models\Service::where('category', 'diagnosis')
            ->where('is_active', true)
            ->first();

        if ($diagnosisService && !$job->quote_id) {
            $this->quoteGeneratorService->generateQuote($job, [
                ['service_id' => $diagnosisService->id, 'quantity' => 1],
            ]);
        }

        $job->update(['status' => 'completed']);
        $releaseService = app(\App\Services\Workflow\ReleaseService::class);
        $releaseService->releaseTechnician($job);
    }
}
