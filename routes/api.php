<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Customer\BookingController;
use App\Http\Controllers\Api\Customer\TrackingController;
use App\Http\Controllers\Api\Admin\TriageController;
use App\Http\Controllers\Api\Admin\ServiceCatalogController;
use App\Http\Controllers\Api\Admin\TechnicianController;
use App\Http\Controllers\Api\Admin\TechnicianSkillController;
use App\Http\Controllers\Api\Admin\SettingsController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\AmcController;
use App\Http\Controllers\Api\Admin\ExpenseController;
use App\Http\Controllers\Api\Admin\PermissionController;
use App\Http\Controllers\Api\Admin\InvoiceController;
use App\Http\Controllers\Api\Admin\TaskController;
use App\Http\Controllers\Api\Admin\PosController;
use App\Http\Controllers\Api\Admin\DeliveryOtpController;
use App\Http\Controllers\Api\Admin\DigitalSignatureController;
use App\Http\Controllers\Api\Admin\LeadController;
use App\Http\Controllers\Api\Admin\OutsourceController;
use App\Http\Controllers\Api\Admin\DataRecoveryController;
use App\Http\Controllers\Api\Admin\SupplierController;
use App\Http\Controllers\Api\Admin\PurchaseOrderController;
use App\Http\Controllers\Api\Admin\LocationController;
use App\Http\Controllers\Api\Admin\TechnicianPerformanceController;
use App\Http\Controllers\Api\Admin\IntegrationController;
use App\Http\Controllers\Api\Admin\BrandingController;
use App\Http\Controllers\Api\Admin\InventoryController as AdminInventoryController;
use App\Http\Controllers\Api\Admin\DeviceImageController;
use App\Http\Controllers\Api\Admin\ReconciliationController;
use App\Http\Controllers\Api\Customer\ServiceHistoryController;
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
        Route::get('/tickets', [TrackingController::class, 'tickets']);
        Route::get('/tickets/{ticketId}/track', [TrackingController::class, 'track']);
        
        // Ratings
        Route::post('/jobs/{jobId}/ratings', [\App\Http\Controllers\Api\Customer\RatingController::class, 'store']);
        Route::get('/jobs/{jobId}/ratings', [\App\Http\Controllers\Api\Customer\RatingController::class, 'show']);
        Route::put('/jobs/{jobId}/ratings', [\App\Http\Controllers\Api\Customer\RatingController::class, 'update']);
        
        // Service History
        Route::get('/service-history', [ServiceHistoryController::class, 'index']);
        Route::get('/service-history/{ticketId}', [ServiceHistoryController::class, 'show']);
    });

    // Technician routes
    Route::middleware('role:technician')->prefix('technician')->name('technician.')->group(function () {
        Route::get('/status', [StatusController::class, 'index']); // GET for current status
        Route::put('/status', [StatusController::class, 'update']);
        Route::put('/location', [StatusController::class, 'updateLocation']);
        
        Route::get('/jobs/offered', [TechnicianJobController::class, 'offered']);
        Route::get('/jobs/assigned', [TechnicianJobController::class, 'assigned']);
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
        Route::put('/technicians/{id}/on-call', [TechnicianController::class, 'updateOnCallStatus']);
        Route::get('/technicians/on-call', [TechnicianController::class, 'getOnCallTechnicians']);
        
        // Technician Skill Management
        Route::get('/technicians/{technicianId}/skills', [TechnicianSkillController::class, 'index']);
        Route::post('/technicians/{technicianId}/skills', [TechnicianSkillController::class, 'store']);
        Route::put('/technicians/{technicianId}/skills/{skillId}', [TechnicianSkillController::class, 'update']);
        Route::delete('/technicians/{technicianId}/skills/{skillId}', [TechnicianSkillController::class, 'destroy']);
        Route::get('/technician-skills/device-types', [TechnicianSkillController::class, 'availableDeviceTypes']);
        Route::get('/technician-skills/match-scores/{ticketId}', [TechnicianSkillController::class, 'getMatchScores']);
        
        Route::get('/settings/white-label', [SettingsController::class, 'getWhiteLabel']);
        Route::put('/settings/white-label', [SettingsController::class, 'updateWhiteLabel']);
        Route::get('/settings/workflow', [SettingsController::class, 'getWorkflow']);
        Route::put('/settings/workflow', [SettingsController::class, 'updateWorkflow']);
        
        // AMC Management
        Route::get('/amc', [AmcController::class, 'index']);
        Route::post('/amc', [AmcController::class, 'store']);
        Route::get('/amc/{id}', [AmcController::class, 'show']);
        Route::put('/amc/{id}', [AmcController::class, 'update']);
        Route::post('/amc/{id}/visit', [AmcController::class, 'recordVisit']);
        Route::get('/amc/expiring-soon', [AmcController::class, 'getExpiringSoon']);
        
        // Expense Management
        Route::get('/expenses', [ExpenseController::class, 'index']);
        Route::post('/expenses', [ExpenseController::class, 'store']);
        Route::get('/expenses/{id}', [ExpenseController::class, 'show']);
        Route::post('/expenses/{id}/approve', [ExpenseController::class, 'approve']);
        Route::post('/expenses/{id}/reject', [ExpenseController::class, 'reject']);
        Route::post('/expenses/{id}/reimbursed', [ExpenseController::class, 'markReimbursed']);
        
        // Permissions Management
        Route::get('/permissions', [PermissionController::class, 'index']);
        Route::post('/permissions', [PermissionController::class, 'store']);
        Route::get('/permissions/role', [PermissionController::class, 'getRolePermissions']);
        Route::post('/permissions/role/assign', [PermissionController::class, 'assignRolePermission']);
        Route::delete('/permissions/role/remove', [PermissionController::class, 'removeRolePermission']);
        Route::get('/permissions/user/{userId}', [PermissionController::class, 'getUserPermissions']);
        Route::post('/permissions/user/assign', [PermissionController::class, 'assignUserPermission']);
        Route::delete('/permissions/user/remove', [PermissionController::class, 'removeUserPermission']);
        
        // Invoice Management
        Route::get('/invoices', [InvoiceController::class, 'index']);
        Route::post('/invoices', [InvoiceController::class, 'store']);
        Route::post('/invoices/from-quote/{quoteId}', [InvoiceController::class, 'fromQuote']);
        Route::get('/invoices/{id}', [InvoiceController::class, 'show']);
        Route::put('/invoices/{id}', [InvoiceController::class, 'update']);
        Route::post('/invoices/{id}/send', [InvoiceController::class, 'markSent']);
        Route::post('/invoices/{id}/payment', [InvoiceController::class, 'recordPayment']);
        
        // Task Management
        Route::get('/tasks', [TaskController::class, 'index']);
        Route::post('/tasks', [TaskController::class, 'store']);
        Route::get('/tasks/{id}', [TaskController::class, 'show']);
        Route::put('/tasks/{id}', [TaskController::class, 'update']);
        Route::post('/tasks/{id}/start', [TaskController::class, 'start']);
        Route::post('/tasks/{id}/complete', [TaskController::class, 'complete']);
        
        // Point of Sale
        Route::get('/pos', [PosController::class, 'index']);
        Route::post('/pos', [PosController::class, 'store']);
        Route::get('/pos/{id}', [PosController::class, 'show']);
        Route::post('/pos/scan', [PosController::class, 'scanBarcode']);
        
        // Delivery OTP
        Route::post('/delivery-otp/{jobId}/generate', [DeliveryOtpController::class, 'generate']);
        Route::post('/delivery-otp/verify', [DeliveryOtpController::class, 'verify']);
        Route::get('/delivery-otp/job/{jobId}', [DeliveryOtpController::class, 'getByJob']);
        
        // Digital Signature
        Route::post('/signatures', [DigitalSignatureController::class, 'store']);
        Route::get('/signatures/{id}', [DigitalSignatureController::class, 'show']);
        Route::get('/signatures', [DigitalSignatureController::class, 'getByDocument']);
        Route::get('/signatures/{id}/verify', [DigitalSignatureController::class, 'verify']);
        
        // Lead Management
        Route::get('/leads', [LeadController::class, 'index']);
        Route::post('/leads', [LeadController::class, 'store']);
        Route::get('/leads/{id}', [LeadController::class, 'show']);
        Route::put('/leads/{id}', [LeadController::class, 'update']);
        Route::post('/leads/{id}/convert', [LeadController::class, 'convertToCustomer']);
        
        // Outsource Management
        Route::get('/outsource/vendors', [OutsourceController::class, 'vendors']);
        Route::post('/outsource/vendors', [OutsourceController::class, 'storeVendor']);
        Route::put('/outsource/vendors/{id}', [OutsourceController::class, 'updateVendor']);
        Route::get('/outsource/requests', [OutsourceController::class, 'requests']);
        Route::post('/outsource/requests', [OutsourceController::class, 'storeRequest']);
        Route::put('/outsource/requests/{id}', [OutsourceController::class, 'updateRequest']);
        
        // Data Recovery
        Route::get('/data-recovery', [DataRecoveryController::class, 'index']);
        Route::post('/data-recovery', [DataRecoveryController::class, 'store']);
        Route::get('/data-recovery/{id}', [DataRecoveryController::class, 'show']);
        Route::put('/data-recovery/{id}', [DataRecoveryController::class, 'update']);
        
        // Supplier Management
        Route::get('/suppliers', [SupplierController::class, 'index']);
        Route::post('/suppliers', [SupplierController::class, 'store']);
        Route::get('/suppliers/{id}', [SupplierController::class, 'show']);
        Route::put('/suppliers/{id}', [SupplierController::class, 'update']);
        
        // Purchase Orders
        Route::get('/purchase-orders', [PurchaseOrderController::class, 'index']);
        Route::post('/purchase-orders', [PurchaseOrderController::class, 'store']);
        Route::get('/purchase-orders/{id}', [PurchaseOrderController::class, 'show']);
        Route::put('/purchase-orders/{id}', [PurchaseOrderController::class, 'update']);
        Route::post('/purchase-orders/{id}/send', [PurchaseOrderController::class, 'markSent']);
        
        // Location Management
        Route::get('/locations', [LocationController::class, 'index']);
        Route::post('/locations', [LocationController::class, 'store']);
        Route::get('/locations/{id}', [LocationController::class, 'show']);
        Route::put('/locations/{id}', [LocationController::class, 'update']);
        
        // Technician Performance
        Route::get('/technician-performance', [TechnicianPerformanceController::class, 'index']);
        Route::post('/technician-performance/{technicianId}/calculate', [TechnicianPerformanceController::class, 'calculate']);
        Route::get('/technician-performance/{id}', [TechnicianPerformanceController::class, 'show']);
        
        // Integrations
        Route::get('/integrations', [IntegrationController::class, 'index']);
        Route::post('/integrations', [IntegrationController::class, 'store']);
        Route::get('/integrations/{id}', [IntegrationController::class, 'show']);
        Route::put('/integrations/{id}', [IntegrationController::class, 'update']);
        Route::post('/integrations/{id}/sync', [IntegrationController::class, 'sync']);
        
        // Branding
        Route::get('/branding', [BrandingController::class, 'show']);
        Route::post('/branding', [BrandingController::class, 'store']);
        Route::put('/branding', [BrandingController::class, 'update']);
        
        // Inventory Management
        Route::get('/inventory', [AdminInventoryController::class, 'index']);
        Route::post('/inventory', [AdminInventoryController::class, 'store']);
        Route::get('/inventory/{id}', [AdminInventoryController::class, 'show']);
        Route::put('/inventory/{id}', [AdminInventoryController::class, 'update']);
        Route::post('/inventory/{id}/adjust', [AdminInventoryController::class, 'adjustStock']);
        Route::get('/inventory/reorder-alerts', [AdminInventoryController::class, 'getReorderAlerts']);
        Route::post('/inventory/scan', [AdminInventoryController::class, 'scanBarcode']);
        
        // Payment Reconciliation
        Route::get('/reconciliation/daily', [ReconciliationController::class, 'daily']);
        Route::get('/reconciliation/unmatched', [ReconciliationController::class, 'unmatchedPayments']);
        
        // Device Images
        Route::post('/tickets/{ticketId}/device-images', [DeviceImageController::class, 'upload']);
        Route::delete('/tickets/{ticketId}/device-images', [DeviceImageController::class, 'delete']);
    });
});
