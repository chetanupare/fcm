<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration fixes the jobs table conflict:
     * - If jobs table exists with service job columns (ticket_id), rename it to service_jobs
     * - Create proper Laravel queue jobs table
     */
    public function up(): void
    {
        // Check if jobs table exists and what columns it has
        if (Schema::hasTable('jobs')) {
            $columns = Schema::getColumnListing('jobs');
            
            // If it has service job columns (ticket_id), it's the service jobs table
            if (in_array('ticket_id', $columns)) {
                // Rename service jobs table to service_jobs
                if (!Schema::hasTable('service_jobs')) {
                    Schema::rename('jobs', 'service_jobs');
                }
                
                // Create Laravel queue jobs table
                if (!Schema::hasTable('jobs')) {
                    Schema::create('jobs', function (Blueprint $table) {
                        $table->id();
                        $table->string('queue')->index();
                        $table->longText('payload');
                        $table->unsignedTinyInteger('attempts');
                        $table->unsignedInteger('reserved_at')->nullable();
                        $table->unsignedInteger('available_at');
                        $table->unsignedInteger('created_at');
                    });
                }
            } 
            // If it's a queue table but missing queue column, add it
            elseif (!in_array('queue', $columns)) {
                Schema::table('jobs', function (Blueprint $table) {
                    if (!Schema::hasColumn('jobs', 'queue')) {
                        $table->string('queue')->index()->after('id');
                    }
                });
            }
        } else {
            // Create queue jobs table if it doesn't exist
            Schema::create('jobs', function (Blueprint $table) {
                $table->id();
                $table->string('queue')->index();
                $table->longText('payload');
                $table->unsignedTinyInteger('attempts');
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't reverse this - it's a fix migration
    }
};
