<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amc_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('device_id')->constrained('devices')->onDelete('cascade');
            $table->string('contract_number')->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('duration_type', ['monthly', 'quarterly', 'semi_annual', 'annual'])->default('annual');
            $table->decimal('contract_amount', 10, 2);
            $table->decimal('service_charge_per_visit', 10, 2)->default(0);
            $table->integer('visits_included')->default(0); // 0 = unlimited
            $table->integer('visits_used')->default(0);
            $table->enum('status', ['active', 'expired', 'cancelled', 'suspended'])->default('active');
            $table->text('terms_and_conditions')->nullable();
            $table->json('covered_services')->nullable(); // Array of service types covered
            $table->json('excluded_services')->nullable(); // Array of service types not covered
            $table->boolean('auto_renew')->default(false);
            $table->date('last_service_date')->nullable();
            $table->date('next_service_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('customer_id');
            $table->index('device_id');
            $table->index('status');
            $table->index('end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amc_contracts');
    }
};
