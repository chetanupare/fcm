<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique(); // e.g., LOC001, BRANCH-A
            $table->text('address');
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('manager_name')->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('type', ['head_office', 'branch', 'warehouse', 'service_center'])->default('branch');
            $table->enum('status', ['active', 'inactive', 'closed'])->default('active');
            $table->json('operating_hours')->nullable(); // Days and hours
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('code');
            $table->index('status');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
