<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outsource_vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone');
            $table->text('address')->nullable();
            $table->string('specialization')->nullable(); // What they specialize in
            $table->json('services_offered')->nullable(); // Array of services
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->decimal('rating', 3, 2)->nullable(); // 0.00 to 5.00
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outsource_vendors');
    }
};
