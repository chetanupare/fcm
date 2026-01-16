<?php

namespace App\Helpers;

use App\Models\Setting;

class CurrencyHelper
{
    /**
     * Format currency amount based on settings
     */
    public static function format(float $amount, ?string $currency = null): string
    {
        $currency = $currency ?? Setting::get('default_currency', 'USD');
        $symbol = Setting::get('currency_symbol', '$');
        $alignment = Setting::get('currency_symbol_alignment', 'left');
        
        $formatted = number_format($amount, 2);
        
        if ($alignment === 'left') {
            return $symbol . $formatted;
        } else {
            return $formatted . $symbol;
        }
    }

    /**
     * Get currency symbol
     */
    public static function symbol(): string
    {
        return Setting::get('currency_symbol', '$');
    }

    /**
     * Get currency code
     */
    public static function code(): string
    {
        return Setting::get('default_currency', 'USD');
    }

    /**
     * Get currency symbol alignment
     */
    public static function alignment(): string
    {
        return Setting::get('currency_symbol_alignment', 'left');
    }
}
