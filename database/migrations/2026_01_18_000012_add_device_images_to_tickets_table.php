<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'device_images')) {
                $table->json('device_images')->nullable()->after('photos'); // Before/After/During repair images
            }
            if (!Schema::hasColumn('tickets', 'device_images_uploaded_at')) {
                $table->timestamp('device_images_uploaded_at')->nullable()->after('device_images');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['device_images', 'device_images_uploaded_at']);
        });
    }
};
