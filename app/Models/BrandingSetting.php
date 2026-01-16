<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandingSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'subdomain',
        'company_name',
        'logo',
        'favicon',
        'primary_color',
        'secondary_color',
        'email',
        'phone',
        'address',
        'website',
        'terms_and_conditions',
        'privacy_policy',
        'social_links',
        'custom_fields',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'custom_fields' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
