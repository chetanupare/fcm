<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\DigitalSignature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DigitalSignatureController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_id' => 'nullable|exists:service_jobs,id',
            'quote_id' => 'nullable|exists:quotes,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'document_type' => 'required|string|in:quote,invoice,contract,work_order,amc,other',
            'signature_image' => 'required|string', // Base64 encoded image
            'notes' => 'nullable|string',
        ]);

        // Save signature image
        $imageData = $validated['signature_image'];
        if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
            $imageData = substr($imageData, strpos($imageData, ',') + 1);
            $type = strtolower($type[1]);
            
            $imageData = base64_decode($imageData);
            $filename = 'signatures/' . uniqid() . '.' . $type;
            Storage::disk('public')->put($filename, $imageData);
            
            $validated['signature_image'] = $filename;
        }

        // Generate hash for verification
        $validated['signature_hash'] = hash('sha256', $imageData);
        $validated['user_id'] = $request->user()->id;
        $validated['signed_at'] = now();
        $validated['ip_address'] = $request->ip();
        $validated['user_agent'] = $request->userAgent();

        $signature = DigitalSignature::create($validated);

        return response()->json($signature, 201);
    }

    public function show($id)
    {
        $signature = DigitalSignature::with(['user', 'job', 'quote', 'invoice'])->findOrFail($id);
        return response()->json($signature);
    }

    public function getByDocument(Request $request)
    {
        $query = DigitalSignature::with(['user']);

        if ($request->has('job_id')) {
            $query->where('job_id', $request->job_id);
        }

        if ($request->has('quote_id')) {
            $query->where('quote_id', $request->quote_id);
        }

        if ($request->has('invoice_id')) {
            $query->where('invoice_id', $request->invoice_id);
        }

        if ($request->has('document_type')) {
            $query->where('document_type', $request->document_type);
        }

        $signatures = $query->orderBy('signed_at', 'desc')->get();

        return response()->json($signatures);
    }

    public function verify($id)
    {
        $signature = DigitalSignature::findOrFail($id);
        
        // Verify hash integrity
        $imageData = Storage::disk('public')->get($signature->signature_image);
        $currentHash = hash('sha256', $imageData);

        return response()->json([
            'is_valid' => $currentHash === $signature->signature_hash,
            'signature' => $signature,
        ]);
    }
}
