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

        Schema::create('data_variations', function (Blueprint $table) {
            $table->id();
            $table->string('service_name');
            $table->string('service_id');
            $table->string('convinience_fee');
            $table->string('variation_code');
            $table->string('name');
            $table->string('variation_amount');
            $table->string('fixedPrice');
            $table->enum('status', ['enabled', 'disabled'])->default('enabled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_variations');
    }
};
