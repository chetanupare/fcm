<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreRatingRequest;
use App\Http\Requests\Customer\UpdateRatingRequest;
use App\Models\Rating;
use App\Models\Job;
use App\Models\Setting;
use Illuminate\Http\Request;

/**
 * @tags Customer
 * 
 * Service ratings and feedback
 */
class RatingController extends Controller
{
    /**
     * Submit a rating for a completed job
     */
    public function store(StoreRatingRequest $request, int $jobId)
    {
        // Check if ratings are enabled
        $ratingsEnabled = Setting::get('enable_service_ratings', true);
        if (!$ratingsEnabled) {
            return response()->json([
                'message' => 'Service ratings are not enabled',
            ], 403);
        }

        $job = Job::whereHas('ticket', function ($q) use ($request) {
            $q->where('customer_id', $request->user()->id);
        })
        ->where('status', 'completed')
        ->with('technician')
        ->findOrFail($jobId);

        // Check if rating already exists
        $existingRating = Rating::where('job_id', $jobId)
            ->where('customer_id', $request->user()->id)
            ->first();

        if ($existingRating) {
            return response()->json([
                'message' => 'Rating already submitted for this job',
            ], 422);
        }

        $validated = $request->validated();

        $rating = Rating::create([
            'job_id' => $job->id,
            'customer_id' => $request->user()->id,
            'technician_id' => $job->technician_id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'aspects' => $validated['aspects'] ?? null,
            'is_visible' => true,
        ]);

        return response()->json([
            'message' => 'Rating submitted successfully',
            'rating' => $rating->load('job', 'technician'),
        ], 201);
    }

    /**
     * Get rating for a job
     */
    public function show(Request $request, int $jobId)
    {
        $rating = Rating::where('job_id', $jobId)
            ->where('customer_id', $request->user()->id)
            ->with(['job', 'technician'])
            ->first();

        if (!$rating) {
            return response()->json([
                'message' => 'Rating not found',
            ], 404);
        }

        return response()->json([
            'rating' => $rating,
        ]);
    }

    /**
     * Update a rating
     */
    public function update(UpdateRatingRequest $request, int $jobId)
    {
        $rating = Rating::where('job_id', $jobId)
            ->where('customer_id', $request->user()->id)
            ->firstOrFail();

        $validated = $request->validated();

        $rating->update($validated);

        return response()->json([
            'message' => 'Rating updated successfully',
            'rating' => $rating->fresh(['job', 'technician']),
        ]);
    }
}
