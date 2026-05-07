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
        Schema::table('scratch_cards', function (Blueprint $table) {
           $table->bigInteger('tnx_id')->nullable();
            $table->string('refno')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scratch_cards', function (Blueprint $table) {
            $table->dropColumn('tnx_id','refno');
        });
    }
};
