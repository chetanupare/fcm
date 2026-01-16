<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('technicians', function (Blueprint $table) {
            if (!Schema::hasColumn('technicians', 'is_on_call')) {
                $table->boolean('is_on_call')->default(false)->after('status');
                $table->index('is_on_call');
            }
        });
    }

    public function down(): void
    {
        Schema::table('technicians', function (Blueprint $table) {
            if (Schema::hasColumn('technicians', 'is_on_call')) {
                $table->dropColumn('is_on_call');
            }
        });
    }
};
