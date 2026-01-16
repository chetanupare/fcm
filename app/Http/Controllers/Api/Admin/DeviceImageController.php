<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DeviceImageController extends Controller
{
    public function upload(Request $request, $ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);

        $validated = $request->validate([
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'required|file|mimes:jpg,jpeg,png|max:5120',
            'image_type' => 'nullable|in:before,during,after',
        ]);

        $uploadedImages = [];
        foreach ($request->file('images') as $image) {
            $path = $image->store('tickets/device-images', 'public');
            $uploadedImages[] = $path;
        }

        $existingImages = $ticket->device_images ?? [];
        $allImages = array_merge($existingImages, $uploadedImages);

        $ticket->update([
            'device_images' => $allImages,
            'device_images_uploaded_at' => now(),
        ]);

        return response()->json([
            'message' => 'Images uploaded successfully',
            'images' => $uploadedImages,
            'ticket' => $ticket->fresh(),
        ], 201);
    }

    public function delete(Request $request, $ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);

        $validated = $request->validate([
            'image_path' => 'required|string',
        ]);

        $images = $ticket->device_images ?? [];
        $images = array_filter($images, fn($img) => $img !== $validated['image_path']);

        Storage::disk('public')->delete($validated['image_path']);

        $ticket->update(['device_images' => array_values($images)]);

        return response()->json([
            'message' => 'Image deleted successfully',
            'ticket' => $ticket->fresh(),
        ]);
    }
}
