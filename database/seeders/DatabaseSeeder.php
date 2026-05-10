<?php

namespace Database\Seeders;

use App\Models\ClaimCount;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {


        // Service::truncate();
        // ClaimCount::truncate();

        User::updateOrCreate(
            ['email' => 'admin@nintrust.com.ng'],
            [
                'name' => 'NIN TRUST Admin',
                'email_verified_at' => now(),
                'password' => Hash::make('@passwd12345'),
                'role' => 'admin',
            ]
        );

        foreach (Service::factory()->withCustomData() as $data) {
            Service::firstOrCreate(
                ['service_code' => $data['service_code']],
                $data
            );
        }

        if (ClaimCount::count() === 0) {
            ClaimCount::factory(1)->create();
        }

        $this->call([
            ReferralBonusTableSeeder::class,
        ]);
    }
}
