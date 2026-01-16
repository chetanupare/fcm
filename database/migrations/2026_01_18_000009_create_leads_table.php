<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('company')->nullable();
            $table->text('address')->nullable();
            $table->enum('source', [
                'website',
                'phone_call',
                'walk_in',
                'referral',
                'social_media',
                'advertisement',
                'email',
                'other'
            ])->default('website');
            $table->enum('status', [
                'new',
                'contacted',
                'qualified',
                'quoted',
                'converted',
                'lost',
                'cancelled'
            ])->default('new');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->decimal('estimated_value', 10, 2)->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->json('tags')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->foreignId('converted_to_customer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('converted_to_ticket_id')->nullable()->constrained('tickets')->onDelete('set null');
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();
            
            $table->index('assigned_to');
            $table->index('status');
            $table->index('source');
            $table->index('priority');
            $table->index('follow_up_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
