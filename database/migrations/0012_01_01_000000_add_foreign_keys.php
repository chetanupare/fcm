<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->foreign('quote_id')->references('id')->on('quotes')->onDelete('set null');
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->foreign('job_id')->references('id')->on('jobs')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropForeign(['quote_id']);
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->dropForeign(['job_id']);
        });
    }
};
