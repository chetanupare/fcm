<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
            $table->foreignId('technician_id')->constrained('technicians')->onDelete('cascade');
            $table->enum('status', [
                'offered',
                'accepted',
                'en_route',
                'arrived',
                'diagnosing',
                'waiting_parts',
                'repairing',
                'quality_check',
                'completed',
                'cancelled',
                'no_show',
                'cannot_repair'
            ])->default('offered');
            $table->timestamp('offer_deadline_at')->nullable();
            $table->timestamp('offer_accepted_at')->nullable();
            $table->foreignId('quote_id')->nullable();
            $table->timestamp('contract_signed_at')->nullable();
            $table->timestamp('payment_received_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->text('after_photo')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
