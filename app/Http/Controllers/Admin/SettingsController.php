<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $whiteLabel = [
            'app_name' => Setting::get('app_name', 'Repair Management'),
            'logo_url' => Setting::get('logo_url'),
            'favicon_url' => Setting::get('favicon_url'),
            'primary_color' => Setting::get('primary_color', '#3B82F6'),
            'secondary_color' => Setting::get('secondary_color', '#1E40AF'),
            'neutral_color' => Setting::get('neutral_color', '#F1F5F9'),
            'color_scheme' => Setting::get('color_scheme', 'custom'),
            'support_email' => Setting::get('support_email'),
            'support_phone' => Setting::get('support_phone'),
            'company_name' => Setting::get('company_name'),
            'company_address' => Setting::get('company_address'),
            'company_website' => Setting::get('company_website'),
            'footer_text' => Setting::get('footer_text'),
            'meta_title' => Setting::get('meta_title'),
            'meta_description' => Setting::get('meta_description'),
            'meta_keywords' => Setting::get('meta_keywords'),
            'og_title' => Setting::get('og_title'),
            'og_description' => Setting::get('og_description'),
            'og_image' => Setting::get('og_image'),
            'twitter_title' => Setting::get('twitter_title'),
            'twitter_description' => Setting::get('twitter_description'),
            'twitter_image' => Setting::get('twitter_image'),
        ];

        $workflow = [
            'triage_timeout_minutes' => Setting::get('triage_timeout_minutes', 5),
            'job_offer_timeout_minutes' => Setting::get('job_offer_timeout_minutes', 5),
            'require_photos' => Setting::get('require_photos', false),
            'tax_rate' => Setting::get('tax_rate', 0),
            'awaiting_payment_timeout_hours' => Setting::get('awaiting_payment_timeout_hours', 24),
            'enable_service_tips' => Setting::get('enable_service_tips', false),
            'enable_service_ratings' => Setting::get('enable_service_ratings', true),
        ];

        $paymentGateways = [
            'stripe_enabled' => Setting::get('stripe_enabled', false),
            'stripe_key' => Setting::get('stripe_key'),
            'stripe_secret' => Setting::get('stripe_secret'),
            'paypal_enabled' => Setting::get('paypal_enabled', false),
            'paypal_client_id' => Setting::get('paypal_client_id'),
            'paypal_secret' => Setting::get('paypal_secret'),
            'razorpay_enabled' => Setting::get('razorpay_enabled', false),
            'razorpay_key' => Setting::get('razorpay_key'),
            'razorpay_secret' => Setting::get('razorpay_secret'),
            'phonepe_enabled' => Setting::get('phonepe_enabled', false),
            'phonepe_merchant_id' => Setting::get('phonepe_merchant_id'),
            'phonepe_salt_key' => Setting::get('phonepe_salt_key'),
            'paytm_enabled' => Setting::get('paytm_enabled', false),
            'paytm_merchant_id' => Setting::get('paytm_merchant_id'),
            'paytm_merchant_key' => Setting::get('paytm_merchant_key'),
            'cash_enabled' => Setting::get('cash_enabled', true),
            'cod_enabled' => Setting::get('cod_enabled', true),
        ];

        $localization = [
            'default_language' => Setting::get('default_language', 'en'),
            'default_currency' => Setting::get('default_currency', 'USD'),
            'currency_symbol' => Setting::get('currency_symbol', '$'),
            'currency_symbol_alignment' => Setting::get('currency_symbol_alignment', 'left'),
            'timezone' => Setting::get('timezone', 'UTC'),
            'date_format' => Setting::get('date_format', 'Y-m-d'),
            'time_format' => Setting::get('time_format', 'H:i'),
        ];

        $system = [
            'upload_image_quality' => Setting::get('upload_image_quality', 85),
            'invoice_generation' => Setting::get('invoice_generation', true),
            'invoice_template' => Setting::get('invoice_template', 'default'),
            'google_maps_api_key' => Setting::get('google_maps_api_key'),
            'google_maps_api_key_restriction' => Setting::get('google_maps_api_key_restriction', 'none'),
            'google_maps_http_restrictions' => Setting::get('google_maps_http_restrictions', ''),
        ];

        $notifications = [
            'sms_provider' => Setting::get('sms_provider', 'twilio'),
            'sms_api_key' => Setting::get('sms_api_key'),
            'sms_api_secret' => Setting::get('sms_api_secret'),
            'sms_from_number' => Setting::get('sms_from_number'),
            'customer_push_enabled' => Setting::get('customer_push_enabled', true),
            'technician_push_enabled' => Setting::get('technician_push_enabled', true),
            'fcm_server_key' => Setting::get('fcm_server_key'),
        ];

        return view('admin.settings.index', compact('whiteLabel', 'workflow', 'paymentGateways', 'localization', 'system', 'notifications'));
    }

    public function updateWhiteLabel(Request $request)
    {
        $request->validate([
            'app_name' => 'sometimes|string|max:255',
            'logo_url' => 'nullable|url',
            'favicon_url' => 'nullable|url',
            'primary_color' => 'sometimes|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'sometimes|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'neutral_color' => 'sometimes|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'color_scheme' => 'sometimes|string|in:electric-blue,deep-indigo,dark-slate,custom',
            'support_email' => 'nullable|email',
            'support_phone' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string',
            'company_website' => 'nullable|url',
            'footer_text' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string|max:500',
            'og_image' => 'nullable|url',
            'twitter_title' => 'nullable|string|max:255',
            'twitter_description' => 'nullable|string|max:500',
            'twitter_image' => 'nullable|url',
        ]);

        // Handle color scheme preset
        if ($request->has('color_scheme') && $request->color_scheme !== 'custom') {
            $schemes = [
                'electric-blue' => [
                    'primary_color' => '#2563EB',
                    'secondary_color' => '#F97316',
                    'neutral_color' => '#F1F5F9',
                ],
                'deep-indigo' => [
                    'primary_color' => '#4F46E5',
                    'secondary_color' => '#06B6D4',
                    'neutral_color' => '#F8FAFC',
                ],
                'dark-slate' => [
                    'primary_color' => '#0F172A',
                    'secondary_color' => '#84CC16',
                    'neutral_color' => '#FFFFFF',
                ],
            ];
            
            if (isset($schemes[$request->color_scheme])) {
                foreach ($schemes[$request->color_scheme] as $key => $value) {
                    Setting::set($key, $value, 'white_label');
                }
            }
            Setting::set('color_scheme', $request->color_scheme, 'white_label');
        } else {
            // Custom colors
            foreach ($request->only([
                'primary_color', 
                'secondary_color',
                'neutral_color'
            ]) as $key => $value) {
                if ($value !== null) {
                    Setting::set($key, $value, 'white_label');
                }
            }
            Setting::set('color_scheme', 'custom', 'white_label');
        }

        foreach ($request->only([
            'app_name', 
            'logo_url', 
            'favicon_url', 
            'support_email',
            'support_phone',
            'company_name',
            'company_address',
            'company_website',
            'footer_text',
            'meta_title',
            'meta_description',
            'meta_keywords',
            'og_title',
            'og_description',
            'og_image',
            'twitter_title',
            'twitter_description',
            'twitter_image'
        ]) as $key => $value) {
            if ($value !== null) {
                Setting::set($key, $value, 'white_label');
            }
        }

        return back()->with('success', 'White label settings updated');
    }

    public function updateWorkflow(Request $request)
    {
        $request->validate([
            'triage_timeout_minutes' => 'sometimes|integer|min:1|max:60',
            'job_offer_timeout_minutes' => 'sometimes|integer|min:1|max:60',
            'require_photos' => 'sometimes|boolean',
            'tax_rate' => 'sometimes|numeric|min:0|max:100',
            'awaiting_payment_timeout_hours' => 'sometimes|integer|min:1|max:168',
            'enable_service_tips' => 'sometimes|boolean',
            'enable_service_ratings' => 'sometimes|boolean',
        ]);

        foreach ($request->only([
            'triage_timeout_minutes', 'job_offer_timeout_minutes', 'require_photos', 'tax_rate',
            'awaiting_payment_timeout_hours', 'enable_service_tips', 'enable_service_ratings'
        ]) as $key => $value) {
            if ($value !== null) {
                Setting::set($key, $value, 'workflow');
            }
        }

        return back()->with('success', 'Workflow settings updated');
    }

    public function updatePaymentGateways(Request $request)
    {
        $request->validate([
            'stripe_enabled' => 'sometimes|boolean',
            'stripe_key' => 'nullable|string',
            'stripe_secret' => 'nullable|string',
            'paypal_enabled' => 'sometimes|boolean',
            'paypal_client_id' => 'nullable|string',
            'paypal_secret' => 'nullable|string',
            'razorpay_enabled' => 'sometimes|boolean',
            'razorpay_key' => 'nullable|string',
            'razorpay_secret' => 'nullable|string',
            'phonepe_enabled' => 'sometimes|boolean',
            'phonepe_merchant_id' => 'nullable|string',
            'phonepe_salt_key' => 'nullable|string',
            'paytm_enabled' => 'sometimes|boolean',
            'paytm_merchant_id' => 'nullable|string',
            'paytm_merchant_key' => 'nullable|string',
            'cash_enabled' => 'sometimes|boolean',
            'cod_enabled' => 'sometimes|boolean',
        ]);

        foreach ($request->only([
            'stripe_enabled', 'stripe_key', 'stripe_secret',
            'paypal_enabled', 'paypal_client_id', 'paypal_secret',
            'razorpay_enabled', 'razorpay_key', 'razorpay_secret',
            'phonepe_enabled', 'phonepe_merchant_id', 'phonepe_salt_key',
            'paytm_enabled', 'paytm_merchant_id', 'paytm_merchant_key',
            'cash_enabled', 'cod_enabled'
        ]) as $key => $value) {
            Setting::set($key, $value ?? false, 'payment');
        }

        return back()->with('success', 'Payment gateway settings updated');
    }

    public function updateLocalization(Request $request)
    {
        $request->validate([
            'default_language' => 'sometimes|string|max:10',
            'default_currency' => 'sometimes|string|max:3',
            'currency_symbol' => 'sometimes|string|max:10',
            'currency_symbol_alignment' => 'sometimes|in:left,right',
            'timezone' => 'sometimes|string|max:50',
            'date_format' => 'sometimes|string|max:20',
            'time_format' => 'sometimes|string|max:20',
        ]);

        foreach ($request->only([
            'default_language', 'default_currency', 'currency_symbol', 'currency_symbol_alignment',
            'timezone', 'date_format', 'time_format'
        ]) as $key => $value) {
            if ($value !== null) {
                Setting::set($key, $value, 'localization');
            }
        }

        return back()->with('success', 'Localization settings updated');
    }

    public function updateSystem(Request $request)
    {
        $request->validate([
            'upload_image_quality' => 'sometimes|integer|min:1|max:100',
            'invoice_generation' => 'sometimes|boolean',
            'invoice_template' => 'sometimes|string|max:50',
            'google_maps_api_key' => 'nullable|string|max:255',
            'google_maps_api_key_restriction' => 'sometimes|in:none,http,ip',
            'google_maps_http_restrictions' => 'nullable|string',
        ]);

        foreach ($request->only([
            'upload_image_quality', 
            'invoice_generation', 
            'invoice_template',
            'google_maps_api_key',
            'google_maps_api_key_restriction',
            'google_maps_http_restrictions'
        ]) as $key => $value) {
            if ($value !== null) {
                Setting::set($key, $value, 'system');
            }
        }

        return back()->with('success', 'System settings updated');
    }

    public function updateNotifications(Request $request)
    {
        $request->validate([
            'sms_provider' => 'sometimes|in:twilio,messagebird',
            'sms_api_key' => 'nullable|string',
            'sms_api_secret' => 'nullable|string',
            'sms_from_number' => 'nullable|string|max:20',
            'customer_push_enabled' => 'sometimes|boolean',
            'technician_push_enabled' => 'sometimes|boolean',
            'fcm_server_key' => 'nullable|string',
        ]);

        foreach ($request->only([
            'sms_provider', 'sms_api_key', 'sms_api_secret', 'sms_from_number',
            'customer_push_enabled', 'technician_push_enabled', 'fcm_server_key'
        ]) as $key => $value) {
            if ($value !== null) {
                Setting::set($key, $value, 'notification');
            }
        }

        return back()->with('success', 'Notification settings updated');
    }
}
