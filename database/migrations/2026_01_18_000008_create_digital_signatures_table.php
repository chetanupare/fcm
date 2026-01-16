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
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Who signed
            $table->foreignId('job_id')->nullable()->constrained('service_jobs')->onDelete('set null');
            $table->foreignId('quote_id')->nullable()->constrained('quotes')->onDelete('set null');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->string('document_type'); // quote, invoice, contract, work_order, etc.
            $table->string('signature_image'); // Base64 or file path
            $table->string('signature_hash')->nullable(); // For verification
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('signed_at');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('job_id');
            $table->index('document_type');
            $table->index('signed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_signatures');
    }
};
