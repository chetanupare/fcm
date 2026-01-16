<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('device_id')->constrained('devices')->onDelete('cascade');
            $table->text('issue_description');
            $table->json('photos')->nullable();
            $table->enum('status', [
                'pending_triage',
                'triage',
                'assigned',
                'accepted',
                'in_progress',
                'on_hold',
                'completed',
                'cancelled'
            ])->default('pending_triage');
            $table->enum('priority', ['normal', 'high'])->default('normal');
            $table->timestamp('triage_deadline_at')->nullable();
            $table->timestamp('triage_handled_at')->nullable();
            $table->boolean('is_warranty')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
