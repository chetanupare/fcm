<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreBookingRequest;
use App\Models\Device;
use App\Models\Setting;
use App\Services\Workflow\TriageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

/**
 * @tags Customer
 * 
 * Customer booking and device management
 */
class BookingController extends Controller
{
    protected TriageService $triageService;

    public function __construct(TriageService $triageService)
    {
        $this->triageService = $triageService;
    }

    /**
     * Create a new booking
     */
    public function store(StoreBookingRequest $request)
    {

        $requirePhotos = Setting::get('require_photos', false);
        if ($requirePhotos && (!$request->hasFile('photos') || count($request->file('photos')) === 0)) {
            return response()->json([
                'message' => 'Photo upload is required',
            ], 422);
        }

        // Find or create device
        $device = Device::firstOrCreate(
            [
                'customer_id' => $request->user()->id,
                'device_type' => $request->device_type,
                'brand' => $request->brand,
                'serial_number' => $request->serial_number,
            ],
            [
                'model' => $request->model,
                'purchase_date' => $request->purchase_date,
            ]
        );

        // Handle photo uploads with quality setting
        $photos = [];
        if ($request->hasFile('photos')) {
            $imageQuality = \App\Models\Setting::get('upload_image_quality', 85);
            
            foreach ($request->file('photos') as $photo) {
                $filename = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                $path = 'tickets/photos/' . $filename;
                $fullPath = storage_path('app/public/' . $path);
                
                // Ensure directory exists
                $directory = dirname($fullPath);
                if (!is_dir($directory)) {
                    mkdir($directory, 0755, true);
                }
                
                // Apply quality compression using Intervention Image
                $image = Image::read($photo);
                $image->save($fullPath, quality: $imageQuality);
                
                $photos[] = $path;
            }
        }

        // Create ticket
        $ticket = $this->triageService->createTicket([
            'customer_id' => $request->user()->id,
            'device_id' => $device->id,
            'issue_description' => $request->issue_description,
            'photos' => $photos,
        ]);

        // Calculate countdown
        $countdown = $ticket->triage_deadline_at 
            ? $ticket->triage_deadline_at->diffInSeconds(now()) 
            : 0;

        return response()->json([
            'ticket' => $ticket->load(['device', 'customer']),
            'countdown' => $countdown,
        ], 201);
    }
}
