<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('service_jobs')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->string('otp', 6);
            $table->enum('type', ['delivery', 'pickup', 'verification'])->default('delivery');
            $table->enum('status', ['pending', 'verified', 'expired', 'used'])->default('pending');
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->string('verified_by')->nullable();
            $table->string('verification_method')->nullable(); // manual, sms, email
            $table->timestamps();
            
            $table->index('job_id');
            $table->index('customer_id');
            $table->index('otp');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_otps');
    }
};
