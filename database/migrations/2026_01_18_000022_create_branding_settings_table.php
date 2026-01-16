<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branding_settings', function (Blueprint $table) {
            $table->id();
            $table->string('subdomain')->unique()->nullable(); // For multi-tenant subdomain support
            $table->string('company_name');
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('primary_color', 7)->default('#3B82F6'); // Hex color
            $table->string('secondary_color', 7)->default('#8B5CF6');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('website')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->text('privacy_policy')->nullable();
            $table->json('social_links')->nullable(); // Facebook, Twitter, etc.
            $table->json('custom_fields')->nullable(); // Additional branding fields
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('subdomain');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branding_settings');
    }
};
