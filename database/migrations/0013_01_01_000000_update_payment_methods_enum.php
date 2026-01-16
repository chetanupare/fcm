<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For MySQL, we need to alter the enum
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE payments MODIFY COLUMN method ENUM('cash', 'stripe', 'paypal', 'cod', 'razorpay', 'phonepe', 'paytm') NOT NULL");
        } else {
            // For PostgreSQL, drop and recreate
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('method');
            });
            
            Schema::table('payments', function (Blueprint $table) {
                $table->enum('method', ['cash', 'stripe', 'paypal', 'cod', 'razorpay', 'phonepe', 'paytm'])->after('currency');
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE payments MODIFY COLUMN method ENUM('cash', 'stripe', 'paypal', 'cod') NOT NULL");
        } else {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('method');
            });
            
            Schema::table('payments', function (Blueprint $table) {
                $table->enum('method', ['cash', 'stripe', 'paypal', 'cod'])->after('currency');
            });
        }
    }
};
