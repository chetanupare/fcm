<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., 'QuickBooks', 'Xero', 'Stripe'
            $table->string('type'); // accounting, payment, crm, inventory, etc.
            $table->enum('status', ['active', 'inactive', 'error'])->default('inactive');
            $table->json('credentials')->nullable(); // Encrypted API keys, tokens, etc.
            $table->json('settings')->nullable(); // Integration-specific settings
            $table->json('mapping')->nullable(); // Field mappings between systems
            $table->timestamp('last_sync_at')->nullable();
            $table->text('last_error')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('name');
            $table->index('type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integrations');
    }
};
