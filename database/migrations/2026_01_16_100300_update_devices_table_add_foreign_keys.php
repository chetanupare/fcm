<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            // Add nullable foreign keys (keeping old columns for backward compatibility)
            if (!Schema::hasColumn('devices', 'device_type_id')) {
                $table->foreignId('device_type_id')->nullable()->after('device_type')->constrained('device_types')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('devices', 'device_brand_id')) {
                $table->foreignId('device_brand_id')->nullable()->after('brand')->constrained('device_brands')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('devices', 'device_model_id')) {
                $table->foreignId('device_model_id')->nullable()->after('model')->constrained('device_models')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            if (Schema::hasColumn('devices', 'device_model_id')) {
                $table->dropForeign(['device_model_id']);
                $table->dropColumn('device_model_id');
            }
            if (Schema::hasColumn('devices', 'device_brand_id')) {
                $table->dropForeign(['device_brand_id']);
                $table->dropColumn('device_brand_id');
            }
            if (Schema::hasColumn('devices', 'device_type_id')) {
                $table->dropForeign(['device_type_id']);
                $table->dropColumn('device_type_id');
            }
        });
    }
};
