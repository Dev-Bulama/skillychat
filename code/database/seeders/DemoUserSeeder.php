<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $email = 'demo@beepost.com';

        $exists = DB::table('users')->where('email', $email)->exists();

        if (! $exists) {
            DB::table('users')->insert([
                'uid' => Str::uuid()->toString(),
                'referral_id' => null,
                'referral_code' => random_int(100000, 999999),
                'auto_subscription_by' => null,
                'created_by' => 1,
                'updated_by' => 1,
                'country_id' => 231,
                'o_auth_id' => null,
                'name' => 'Demo User',
                'username' => 'demo_user',
                'phone' => '011242365',
                'balance' => 18.00,
                'email' => $email,
                'notification_settings' => null,
                'settings' => null,
                'address' => json_encode([
                    'city' => null,
                    'state' => null,
                    'postal_code' => null,
                    'address' => null,
                ]),
                'email_verified_at' => $now,
                'status' => '1',
                'created_at' => $now,
                'updated_at' => $now,
                'password' => Hash::make('password'),
                'auto_subscription' => 'off',
                'is_kyc_verified' => '1',
                'webhook_api_key' => Str::random(32),
                'last_login' => $now,
                'remember_token' => Str::random(60),
            ]);
        }
    }
}
