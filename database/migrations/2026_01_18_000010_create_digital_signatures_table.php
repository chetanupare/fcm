<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('job_id')->nullable()->constrained('service_jobs')->onDelete('set null');
            $table->foreignId('quote_id')->nullable()->constrained('quotes')->onDelete('set null');
            // invoice_id will be added after invoices table is created (migration 000025)
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->string('document_type'); // quote, invoice, contract, work_order, amc, other
            $table->string('signature_image'); // Path to stored image
            $table->string('signature_hash'); // SHA256 hash for verification
            $table->timestamp('signed_at');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('job_id');
            $table->index('quote_id');
            $table->index('invoice_id');
            $table->index('document_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_signatures');
    }
};
