<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->index('service_code');
            $table->index('status');
        });

        Schema::table('verifications', function (Blueprint $table) {
            $table->index('idno');
            $table->index('type');
            $table->index('trackingId');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex(['service_code']);
            $table->dropIndex(['status']);
        });

        Schema::table('verifications', function (Blueprint $table) {
            $table->dropIndex(['idno']);
            $table->dropIndex(['type']);
            $table->dropIndex(['trackingId']);
        });
    }
};
