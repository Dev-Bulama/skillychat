<?php

namespace Database\Seeders;

use App\Enums\StatusEnum;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SocialAccountsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $superAdmin = Admin::where('super_admin' , StatusEnum::true->status())->first();
        $demo_user  = User::where('username' , "demo_user")->first();
        $subscription =  $demo_user->runningSubscription;



        $demoAccounts = [
            // Facebook Demo Pages
            [
                'platform_id' => 1,
                'name' => 'Demo Facebook Page 1',
                'account_id' => 'fb_demo_1',
                'account_type' => '1', // Page
                'avatar' => 'https://placehold.co/100x100/facebook',
            ],
            [
                'platform_id' => 1,
                'name' => 'Demo Facebook Page 2',
                'account_id' => 'fb_demo_2',
                'account_type' => '1',
                'avatar' => 'https://placehold.co/100x100/facebook',
            ],

            // Instagram Demo Profile
            [
                'platform_id' => 2,
                'name' => 'Demo Instagram Profile',
                'account_id' => 'ig_demo_1',
                'account_type' => '0', // Profile
                'avatar' => 'https://placehold.co/100x100/instagram',
            ],

            // Twitter Demo Account
            [
                'platform_id' => 3,
                'name' => 'Demo Twitter Account',
                'account_id' => 'tw_demo_1',
                'account_type' => '0', // Profile
                'avatar' => 'https://placehold.co/100x100/twitter',
            ],

            // LinkedIn Demo Company Page
            [
                'platform_id' => 4,
                'name' => 'Demo LinkedIn Page',
                'account_id' => 'ln_demo_1',
                'account_type' => '1', // Page
                'avatar' => 'https://placehold.co/100x100/linkedin',
            ],

            // TikTok Demo Profile
            [
                'platform_id' => 5,
                'name' => 'Demo TikTok Account',
                'account_id' => 'tt_demo_1',
                'account_type' => '0', // Profile
                'avatar' => 'https://placehold.co/100x100/tiktok',
            ],

            [
                'platform_id' => 6,
                'name' => 'Demo YouTube Channel',
                'account_id' => 'yt_demo_1',
                'account_type' => '1', // Channel as Page
                'avatar' => 'https://placehold.co/100x100/youtube',
            ],
        ];

        foreach ($demoAccounts as $account) {
            DB::table('social_accounts')->insert([
                'uid' => Str::uuid()->toString(),
                'platform_id' => $account['platform_id'],
                'subscription_id' => $subscription->id ?? null,
                'user_id' => $demo_user->id,
                'admin_id' => $superAdmin->id,
                'name' => $account['name'],
                'account_id' => $account['account_id'],
                'account_information' => json_encode([
                    'id' => $account['account_id'],
                    'name' => $account['name'],
                    'avatar' => $account['avatar'],
                ]),
                'status' => '1',
                'is_official' => '0',
                'is_connected' => '1',
                'account_type' => $account['account_type'],
                'token' => 'demo_token',
                'access_token_expire_at' => null,
                'refresh_token' => null,
                'refresh_token_expire_at' => null,
                'details' => 'Demo account',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
