<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('alternate_phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('website')->nullable();
            $table->string('tax_id')->nullable();
            $table->enum('payment_terms', ['net_15', 'net_30', 'net_45', 'net_60', 'cod', 'prepaid'])->default('net_30');
            $table->decimal('credit_limit', 10, 2)->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->text('notes')->nullable();
            $table->json('specializations')->nullable(); // Types of components they supply
            $table->timestamps();
            
            $table->index('status');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
