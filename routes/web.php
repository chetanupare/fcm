<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TriageController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\TechnicianController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ComponentController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return redirect()->route('admin.login');
});

// Test route
Route::get('/test-admin-login', function () {
    return view('auth.login');
});

// Admin Authentication
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
});

// Protected Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Triage Management
    Route::get('/triage', [TriageController::class, 'index'])->name('triage.index');
    Route::post('/triage/{ticketId}/assign', [TriageController::class, 'assign'])->name('triage.assign');
    Route::post('/triage/{ticketId}/reject', [TriageController::class, 'reject'])->name('triage.reject');
    
    // Service Catalog
    Route::resource('services', ServiceController::class);
    
    // Technicians
    Route::get('/technicians', [TechnicianController::class, 'index'])->name('technicians.index');
    Route::get('/technicians/map', [TechnicianController::class, 'map'])->name('technicians.map');
    Route::get('/technicians/{id}/revenue', [TechnicianController::class, 'revenue'])->name('technicians.revenue');
    
    // Customers Management
    Route::resource('customers', CustomerController::class);
    
    // Components Management
    Route::resource('components', ComponentController::class);
    Route::get('/components/trends', [ComponentController::class, 'trends'])->name('components.trends');
    
    // Component Categories & Brands
    Route::get('/component-categories', [ComponentController::class, 'categories'])->name('components.categories');
    Route::get('/component-brands', [ComponentController::class, 'brands'])->name('components.brands');
    
           // Settings
           Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
           Route::post('/settings/white-label', [SettingsController::class, 'updateWhiteLabel'])->name('settings.white-label');
           Route::post('/settings/workflow', [SettingsController::class, 'updateWorkflow'])->name('settings.workflow');
           Route::post('/settings/payment-gateways', [SettingsController::class, 'updatePaymentGateways'])->name('settings.payment-gateways');
           Route::post('/settings/localization', [SettingsController::class, 'updateLocalization'])->name('settings.localization');
           Route::post('/settings/system', [SettingsController::class, 'updateSystem'])->name('settings.system');
           Route::post('/settings/notifications', [SettingsController::class, 'updateNotifications'])->name('settings.notifications');
           
           // Reports
           Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
           Route::get('/reports/revenue', [\App\Http\Controllers\Admin\ReportController::class, 'revenue'])->name('reports.revenue');
           Route::get('/reports/technician', [\App\Http\Controllers\Admin\ReportController::class, 'technician'])->name('reports.technician');
           Route::get('/reports/component', [\App\Http\Controllers\Admin\ReportController::class, 'component'])->name('reports.component');
           Route::get('/reports/customer', [\App\Http\Controllers\Admin\ReportController::class, 'customer'])->name('reports.customer');
           
           // Exports
           Route::get('/export/customers', [\App\Http\Controllers\Admin\ExportController::class, 'customers'])->name('export.customers');
           Route::get('/export/components', [\App\Http\Controllers\Admin\ExportController::class, 'components'])->name('export.components');
           Route::get('/export/jobs', [\App\Http\Controllers\Admin\ExportController::class, 'jobs'])->name('export.jobs');
       });
