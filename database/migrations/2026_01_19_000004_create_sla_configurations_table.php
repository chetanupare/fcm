<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., 'Critical', 'High', 'Normal', 'Low'
            $table->enum('priority', ['critical', 'high', 'normal', 'low'])->unique();
            $table->integer('triage_minutes')->default(5); // Time to triage
            $table->integer('assignment_minutes')->default(15); // Time to assign
            $table->integer('response_minutes')->default(60); // Time to first response
            $table->integer('resolution_minutes')->default(1440); // Time to resolution (24 hours default)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('priority');
            $table->index('is_active');
        });

        // Insert default SLA configurations
        DB::table('sla_configurations')->insert([
            [
                'name' => 'Critical',
                'priority' => 'critical',
                'triage_minutes' => 2,
                'assignment_minutes' => 5,
                'response_minutes' => 15,
                'resolution_minutes' => 240, // 4 hours
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'High',
                'priority' => 'high',
                'triage_minutes' => 5,
                'assignment_minutes' => 15,
                'response_minutes' => 60,
                'resolution_minutes' => 480, // 8 hours
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Normal',
                'priority' => 'normal',
                'triage_minutes' => 15,
                'assignment_minutes' => 60,
                'response_minutes' => 240,
                'resolution_minutes' => 1440, // 24 hours
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Low',
                'priority' => 'low',
                'triage_minutes' => 60,
                'assignment_minutes' => 240,
                'response_minutes' => 480,
                'resolution_minutes' => 2880, // 48 hours
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_configurations');
    }
};
