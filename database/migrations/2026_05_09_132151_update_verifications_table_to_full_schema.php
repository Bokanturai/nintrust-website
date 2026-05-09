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
        Schema::table('verifications', function (Blueprint $table) {
            // Add missing columns from user's provided schema
            if (!Schema::hasColumn('verifications', 'reference')) {
                $table->string('reference')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('verifications', 'service_field_id')) {
                $table->unsignedBigInteger('service_field_id')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('verifications', 'service_id')) {
                $table->unsignedBigInteger('service_id')->nullable()->after('service_field_id');
            }
            
            // Normalize names to match user's request (keeping old ones for compatibility)
            if (!Schema::hasColumn('verifications', 'firstname')) {
                $table->string('firstname')->nullable();
            }
            if (!Schema::hasColumn('verifications', 'middlename')) {
                $table->string('middlename')->nullable();
            }
            if (!Schema::hasColumn('verifications', 'surname')) {
                $table->string('surname')->nullable();
            }
            if (!Schema::hasColumn('verifications', 'birthdate')) {
                $table->string('birthdate')->nullable();
            }
            if (!Schema::hasColumn('verifications', 'telephoneno')) {
                $table->string('telephoneno')->nullable();
            }

            // Additional details
            $table->string('birthstate')->nullable();
            $table->string('birthlga')->nullable();
            $table->string('birthcountry')->nullable();
            $table->string('maritalstatus')->nullable();
            $table->string('registrationDate')->nullable();
            $table->string('enrollmentBank')->nullable();
            $table->string('enrollmentBranch')->nullable();
            $table->string('watchListed')->nullable();
            $table->string('levelOfAccount')->nullable();
            $table->string('stateOfResidence')->nullable();
            $table->string('lgaOfResidence')->nullable();
            $table->string('residentialAddress')->nullable();
            $table->string('residence_address')->nullable();
            $table->string('religion')->nullable();
            $table->string('employmentstatus')->nullable();
            $table->string('educationallevel')->nullable();
            $table->string('profession')->nullable();
            $table->string('heigth')->nullable();
            $table->string('number_nin')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('tax_residency')->nullable();
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->string('vnin')->nullable();
            $table->longText('photo_path')->nullable();
            $table->longText('signature_path')->nullable();
            $table->string('userid')->nullable();
            $table->string('performed_by', 150)->nullable();
            $table->string('approved_by', 150)->nullable();
            
            // Next of Kin
            $table->string('nok_firstname')->nullable();
            $table->string('nok_middlename')->nullable();
            $table->string('nok_surname')->nullable();
            $table->string('nok_address1')->nullable();
            $table->string('nok_address2')->nullable();
            $table->string('nok_lga')->nullable();
            $table->string('nok_state')->nullable();
            $table->string('nok_town')->nullable();
            $table->string('nok_postalcode')->nullable();
            
            // Origin details
            $table->string('self_origin_state')->nullable();
            $table->string('self_origin_lga')->nullable();
            $table->string('self_origin_place')->nullable();
            
            // Transaction & Status
            if (!Schema::hasColumn('verifications', 'transaction_id')) {
                $table->unsignedBigInteger('transaction_id')->nullable();
            }
            if (!Schema::hasColumn('verifications', 'status')) {
                $table->enum('status', ['pending', 'processing', 'resolved', 'rejected', 'successful', 'failed', 'not_found'])->default('pending');
            }
            if (!Schema::hasColumn('verifications', 'submission_date')) {
                $table->datetime('submission_date')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('verifications', function (Blueprint $table) {
            // Reversing might be complex, but usually we just leave it or drop columns
            // For safety in this environment, I'll leave the down() empty or minimal
        });
    }
};
