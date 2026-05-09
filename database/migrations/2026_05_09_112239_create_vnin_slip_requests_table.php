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
        Schema::create('vnin_slip_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('tnx_id')->nullable();
            $table->string('refno')->nullable();

            $table->string('nin')->nullable();
            
            $table->enum('status', ['submitted', 'processing', 'successful', 'rejected'])->default('submitted');
            $table->text('reason')->nullable(); // comment/response from admin
            $table->timestamp('refunded_at')->nullable();
            $table->string('result_file')->nullable();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vnin_slip_requests');
    }
};
