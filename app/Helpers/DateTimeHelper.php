<?php

namespace App\Helpers;

use App\Models\Setting;
use Carbon\Carbon;

class DateTimeHelper
{
    /**
     * Format date based on settings
     */
    public static function formatDate($date, ?string $format = null): string
    {
        if (!$date) {
            return '';
        }

        $format = $format ?? Setting::get('date_format', 'Y-m-d');
        $timezone = Setting::get('timezone', 'UTC');
        
        if ($date instanceof Carbon) {
            return $date->setTimezone($timezone)->format($format);
        }
        
        return Carbon::parse($date)->setTimezone($timezone)->format($format);
    }

    /**
     * Format time based on settings
     */
    public static function formatTime($date, ?string $format = null): string
    {
        if (!$date) {
            return '';
        }

        $format = $format ?? Setting::get('time_format', 'H:i');
        $timezone = Setting::get('timezone', 'UTC');
        
        if ($date instanceof Carbon) {
            return $date->setTimezone($timezone)->format($format);
        }
        
        return Carbon::parse($date)->setTimezone($timezone)->format($format);
    }

    /**
     * Format date and time based on settings
     */
    public static function formatDateTime($date): string
    {
        if (!$date) {
            return '';
        }

        $dateFormat = Setting::get('date_format', 'Y-m-d');
        $timeFormat = Setting::get('time_format', 'H:i');
        $timezone = Setting::get('timezone', 'UTC');
        
        $format = $dateFormat . ' ' . $timeFormat;
        
        if ($date instanceof Carbon) {
            return $date->setTimezone($timezone)->format($format);
        }
        
        return Carbon::parse($date)->setTimezone($timezone)->format($format);
    }

    /**
     * Get timezone
     */
    public static function timezone(): string
    {
        return Setting::get('timezone', 'UTC');
    }

    /**
     * Convert to application timezone
     */
    public static function toAppTimezone($date): Carbon
    {
        $timezone = Setting::get('timezone', 'UTC');
        
        if ($date instanceof Carbon) {
            return $date->setTimezone($timezone);
        }
        
        return Carbon::parse($date)->setTimezone($timezone);
    }
}
