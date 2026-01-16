<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Customer\BookingController;
use App\Http\Controllers\Api\Customer\TrackingController;
use App\Http\Controllers\Api\Admin\TriageController;
use App\Http\Controllers\Api\Admin\ServiceCatalogController;
use App\Http\Controllers\Api\Admin\TechnicianController;
use App\Http\Controllers\Api\Admin\SettingsController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Technician\JobController as TechnicianJobController;
use App\Http\Controllers\Api\Technician\StatusController;
use App\Http\Controllers\Api\Technician\InventoryController;
use App\Http\Controllers\Api\Technician\ChecklistController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\DeviceController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/settings/white-label', [SettingsController::class, 'getWhiteLabel']); // Public endpoint for color scheme

// Device data (public - no auth required)
Route::get('/device-types', [DeviceController::class, 'getDeviceTypes']);
Route::get('/device-brands', [DeviceController::class, 'getBrands']);
Route::get('/device-models', [DeviceController::class, 'getModels']);
Route::get('/devices/all', [DeviceController::class, 'getAll']); // Get all types, brands, models in one call

// Webhooks (no auth required)
Route::post('/webhooks/stripe', [WebhookController::class, 'stripe']);
Route::post('/webhooks/paypal', [WebhookController::class, 'paypal']);
Route::post('/webhooks/razorpay', [WebhookController::class, 'razorpay']);
Route::post('/webhooks/phonepe', [WebhookController::class, 'phonepe']);
Route::post('/webhooks/paytm', [WebhookController::class, 'paytm']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Customer routes
    Route::middleware('role:customer')->prefix('customer')->name('customer.')->group(function () {
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::get('/devices', [TrackingController::class, 'devices']);
        Route::get('/tickets/{ticketId}/track', [TrackingController::class, 'track']);
        
        // Ratings
        Route::post('/jobs/{jobId}/ratings', [\App\Http\Controllers\Api\Customer\RatingController::class, 'store']);
        Route::get('/jobs/{jobId}/ratings', [\App\Http\Controllers\Api\Customer\RatingController::class, 'show']);
        Route::put('/jobs/{jobId}/ratings', [\App\Http\Controllers\Api\Customer\RatingController::class, 'update']);
    });

    // Technician routes
    Route::middleware('role:technician')->prefix('technician')->name('technician.')->group(function () {
        Route::get('/status', [StatusController::class, 'index']); // GET for current status
        Route::put('/status', [StatusController::class, 'update']);
        Route::put('/location', [StatusController::class, 'updateLocation']);
        
        Route::get('/jobs/offered', [TechnicianJobController::class, 'offered']);
        Route::post('/jobs/{id}/accept', [TechnicianJobController::class, 'accept']);
        Route::post('/jobs/{id}/reject', [TechnicianJobController::class, 'reject']);
        Route::get('/jobs/{id}', [TechnicianJobController::class, 'show']);
        Route::post('/jobs/{id}/generate-quote', [TechnicianJobController::class, 'generateQuote']);
        Route::post('/jobs/{id}/sign-contract', [TechnicianJobController::class, 'signContract']);
        Route::put('/jobs/{id}/status', [TechnicianJobController::class, 'updateStatus']);
        Route::post('/jobs/{id}/after-photo', [TechnicianJobController::class, 'uploadAfterPhoto']);
        Route::post('/jobs/{id}/payment/initiate', [PaymentController::class, 'initiatePayment']);
        Route::post('/jobs/{id}/payment', [TechnicianJobController::class, 'recordPayment']);
        
        Route::get('/jobs/{jobId}/checklist', [ChecklistController::class, 'index']);
        Route::post('/jobs/{jobId}/checklist/{checklistId}/complete', [ChecklistController::class, 'complete']);
        
        Route::post('/jobs/{jobId}/on-hold', [InventoryController::class, 'markOnHold']);
    });

    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);
        
        Route::get('/triage', [TriageController::class, 'index']);
        Route::post('/triage/{ticketId}/assign', [TriageController::class, 'assign']);
        Route::post('/triage/{ticketId}/reject', [TriageController::class, 'reject']);
        
        Route::get('/services', [ServiceCatalogController::class, 'index']);
        Route::post('/services', [ServiceCatalogController::class, 'store']);
        Route::put('/services/{id}', [ServiceCatalogController::class, 'update']);
        Route::delete('/services/{id}', [ServiceCatalogController::class, 'destroy']);
        
        Route::get('/technicians', [TechnicianController::class, 'index']);
        Route::get('/technicians/{id}/revenue', [TechnicianController::class, 'revenue']);
        Route::get('/map', [TechnicianController::class, 'map']);
        
        Route::get('/settings/white-label', [SettingsController::class, 'getWhiteLabel']);
        Route::put('/settings/white-label', [SettingsController::class, 'updateWhiteLabel']);
        Route::get('/settings/workflow', [SettingsController::class, 'getWorkflow']);
        Route::put('/settings/workflow', [SettingsController::class, 'updateWorkflow']);
    });
});
