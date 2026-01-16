<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_sla_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
            $table->foreignId('sla_configuration_id')->nullable()->constrained('sla_configurations')->onDelete('set null');
            $table->enum('priority', ['critical', 'high', 'normal', 'low'])->default('normal');
            
            // Milestone timestamps
            $table->timestamp('triage_deadline_at')->nullable();
            $table->timestamp('triage_completed_at')->nullable();
            $table->timestamp('assignment_deadline_at')->nullable();
            $table->timestamp('assignment_completed_at')->nullable();
            $table->timestamp('response_deadline_at')->nullable();
            $table->timestamp('response_completed_at')->nullable();
            $table->timestamp('resolution_deadline_at')->nullable();
            $table->timestamp('resolution_completed_at')->nullable();
            
            // SLA status
            $table->enum('triage_status', ['pending', 'on_time', 'at_risk', 'breached'])->default('pending');
            $table->enum('assignment_status', ['pending', 'on_time', 'at_risk', 'breached'])->default('pending');
            $table->enum('response_status', ['pending', 'on_time', 'at_risk', 'breached'])->default('pending');
            $table->enum('resolution_status', ['pending', 'on_time', 'at_risk', 'breached'])->default('pending');
            
            // Escalation tracking
            $table->integer('escalation_level')->default(0); // 0 = none, 1 = supervisor, 2 = manager, 3 = executive
            $table->timestamp('last_escalated_at')->nullable();
            $table->json('escalation_history')->nullable(); // Array of escalation events
            
            $table->timestamps();
            
            $table->index('ticket_id');
            $table->index('priority');
            $table->index('triage_status');
            $table->index('assignment_status');
            $table->index('response_status');
            $table->index('resolution_status');
            $table->index('escalation_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_sla_tracking');
    }
};
