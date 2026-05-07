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
        Schema::create('suspended_nin_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('tnx_id')->nullable();
            $table->string('refno')->nullable();

            $table->string('title')->nullable();
            $table->string('nin')->nullable();
            $table->string('surname');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('gender')->nullable();
            $table->date('dob')->nullable();
            $table->string('town_city')->nullable();
            $table->string('state_residence')->nullable();
            $table->string('lga_residence')->nullable();
            $table->text('address_residence')->nullable();
            $table->string('state_origin')->nullable();
            $table->string('lga_origin')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('photo')->nullable();

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
        Schema::dropIfExists('suspended_nin_requests');
    }
};
