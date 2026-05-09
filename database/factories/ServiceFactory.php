<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
        ];
    }

    public function withCustomData()
    {
        return [

            [
                'service_code' => '101',
                'name' => 'BVN Verification',
                'category' => 'Verifications',
                'type' => 'Uncategorized',
                'amount' => 100.00,
                'description' => 'BVN Verification Fee',
                'status' => 'enabled',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'service_code' => '102',
                'name' => 'Standard Slip',
                'category' => 'Verifications',
                'type' => 'Uncategorized',
                'amount' => 50.00,
                'description' => 'BVN Standard Slip',
                'status' => 'enabled',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'service_code' => '103',
                'name' => 'BVN Premium Slip',
                'category' => 'Verifications',
                'type' => 'Uncategorized',
                'amount' => 100.00,
                'description' => 'BVN Premium Slip FEE',
                'status' => 'enabled',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'service_code' => '104',
                'name' => 'NIN Verification',
                'category' => 'Verifications',
                'type' => 'Uncategorized',
                'amount' => 150.00,
                'description' => 'NIN Verification fee',
                'status' => 'enabled',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'service_code' => '105',
                'name' => 'Regular NIN Slip',
                'category' => 'Verifications',
                'type' => 'Uncategorized',
                'amount' => 50.00,
                'description' => 'Regular NIN Slip',
                'status' => 'enabled',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'service_code' => '106',
                'name' => 'Standard NIN Slip',
                'category' => 'Verifications',
                'type' => 'Uncategorized',
                'amount' => 150.00,
                'description' => 'Standard NIN Slip',
                'status' => 'enabled',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'service_code' => '107',
                'name' => 'Premium NIN Slip',
                'category' => 'Verifications',
                'type' => 'Uncategorized',
                'amount' => 200.00,
                'description' => 'Premium NIN Slip',
                'status' => 'enabled',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'service_code' => '108',
                'name' => 'NIN Personalization',
                'category' => 'Verifications',
                'type' => null,
                'amount' => 300.00,
                'description' => 'NIN Verification fee using tracking No',
                'status' => 'enabled',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'service_code' => '109',
                'name' => 'BVN Plastic ID',
                'category' => 'Verifications',
                'type' => 'Uncategorized',
                'amount' => 200.00,
                'description' => 'BVN Plastic ID FEE',
                'status' => 'enabled',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'service_code' => '110',
                'name' => 'BVN Enrollment',
                'category' => 'Agency',
                'type' => 'Uncategorized',
                'amount' => 10000.00,
                'description' => 'BVN Enrollment FEE',
                'status' => 'enabled',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'service_code' => '111',
                'name' => 'NIN Phone Verification',
                'category' => 'Verifications',
                'type' => 'Uncategorized',
                'amount' => 100.00,
                'description' => 'NIN Phone Verification FEE',
                'status' => 'enabled',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'service_code' => '112',
                'name' => 'IPE Request',
                'category' => 'Verifications',
                'type' => 'Uncategorized',
                'amount' => 500.00,
                'description' => 'IPE Request FEE',
                'status' => 'enabled',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'service_code' => '127',
                'name' => 'Basic NIN Slip',
                'category' => 'Verifications',
                'type' => 'Uncategorized',
                'amount' => 50.00,
                'description' => 'Basic NIN Slip FEE',
                'status' => 'enabled',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'service_code' => '128',
                'name' => 'NIN V2 Verification',
                'category' => 'Verifications',
                'type' => 'Uncategorized',
                'amount' => 150.00,
                'description' => 'NIN V2 Verification FEE',
                'status' => 'enabled',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'service_code' => '133',
                'name' => 'NIN Service 133',
                'category' => 'Verifications',
                'type' => 'Uncategorized',
                'amount' => 200.00,
                'description' => 'NIN Service 133 FEE',
                'status' => 'enabled',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'service_code' => '134',
                'name' => 'BVN Service 134',
                'category' => 'Verifications',
                'type' => 'Uncategorized',
                'amount' => 200.00,
                'description' => 'BVN Service 134 FEE',
                'status' => 'enabled',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'service_code' => 'V101',
                'name' => 'VNIN Slip',
                'category' => 'Verifications',
                'type' => 'Uncategorized',
                'amount' => 500.00,
                'description' => 'VNIN Slip Fee',
                'status' => 'enabled',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'service_code' => '1111',
                'name' => 'Suspended NIN',
                'category' => 'Verifications',
                'type' => 'Suspended NIN',
                'amount' => 500.00,
                'description' => 'Suspended NIN Fee',
                'status' => 'enabled',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];
    }
}
