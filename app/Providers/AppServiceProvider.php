<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Models\Setting;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set application timezone from settings (only if database is available)
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $timezone = Setting::get('timezone', config('app.timezone', 'UTC'));
                config(['app.timezone' => $timezone]);
                date_default_timezone_set($timezone);
            }
        } catch (\Exception $e) {
            // Database not available yet (e.g., during migrations)
            // Use default timezone
            $timezone = config('app.timezone', 'UTC');
            date_default_timezone_set($timezone);
        }

        // Register Blade directives for currency formatting
        Blade::directive('currency', function ($expression) {
            return "<?php echo \App\Helpers\CurrencyHelper::format($expression); ?>";
        });

        // Register Blade directives for date formatting
        Blade::directive('formatDate', function ($expression) {
            return "<?php echo \App\Helpers\DateTimeHelper::formatDate($expression); ?>";
        });

        Blade::directive('formatTime', function ($expression) {
            return "<?php echo \App\Helpers\DateTimeHelper::formatTime($expression); ?>";
        });

        Blade::directive('formatDateTime', function ($expression) {
            return "<?php echo \App\Helpers\DateTimeHelper::formatDateTime($expression); ?>";
        });
    }
}
