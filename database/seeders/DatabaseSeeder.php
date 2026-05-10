<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            ReferralBonusTableSeeder::class,
        ]);

        // Seed CRM Service
        Service::updateOrCreate(
            ['service_code' => '021'],
            [
                'name' => 'Central risk management CRM',
                'category' => 'Agency',
                'type' => 'CRM',
                'amount' => 500.00,
                'description' => 'BVN CRM Services (Central risk management)',
                'status' => 'enabled',
            ]
        );

        // Seed Manual BVN Search Service
        Service::updateOrCreate(
            ['service_code' => '046'],
            [
                'name' => 'Manual BVN Search',
                'category' => 'Agency',
                'type' => 'MANUAL_BVN_SEARCH',
                'amount' => 500.00,
                'description' => 'Manual BVN Search (No API - Admin Handled)',
                'status' => 'enabled',
            ]
        );

        // Seed BVN Search Service
        Service::updateOrCreate(
            ['service_code' => '045'],
            [
                'name'        => 'BVN Search Request',
                'category'    => 'Agency',
                'type'        => 'BVN_SEARCH',
                'amount'      => 500.00,
                'description' => 'BVN Phone Search Service (Arewa API)',
                'status'      => 'enabled',
            ]
        );
    }
}
