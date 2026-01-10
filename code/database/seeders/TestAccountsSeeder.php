<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Package;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Test Admin
        $admin = Admin::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'uid' => Str::uuid(),
                'name' => 'Test Admin',
                'username' => 'testadmin',
                'email' => 'admin@test.com',
                'password' => Hash::make('password123'),
                'phone' => '+1234567890',
                'status' => 1,
                'role_id' => null, // Super admin
                'created_by' => null,
            ]
        );

        $this->command->info('✓ Test Admin Created');
        $this->command->info('  Email: admin@test.com');
        $this->command->info('  Password: password123');
        $this->command->line('');

        // Create Test User
        $user = User::firstOrCreate(
            ['email' => 'user@test.com'],
            [
                'uid' => Str::uuid(),
                'name' => 'Test User',
                'user_name' => 'testuser',
                'email' => 'user@test.com',
                'password' => Hash::make('password123'),
                'phone' => '+1234567891',
                'status' => 1,
                'kyc_verified' => 1,
                'created_by' => null,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('✓ Test User Created');
        $this->command->info('  Email: user@test.com');
        $this->command->info('  Password: password123');
        $this->command->line('');

        // Create or Update Test Package with Chatbot Features
        $package = Package::firstOrCreate(
            ['name' => 'Chatbot Test Package'],
            [
                'uid' => Str::uuid(),
                'name' => 'Chatbot Test Package',
                'slug' => 'chatbot-test-package',
                'price' => 0.00,
                'discount_price' => 0.00,
                'duration' => 'unlimited',
                'status' => 1,
                'is_free' => 1,
                'is_recommended' => 0,
                'is_feature' => 0,

                // AI Features (existing)
                'word_balance' => 100000,
                'image_balance' => 1000,
                'video_balance' => 100,
                'total_profile' => 10,
                'post_balance' => 1000,
                'template_access' => json_encode([]),
                'image_template_access' => json_encode([]),
                'video_template_access' => json_encode([]),
                'social_access' => json_encode([]),
                'ai_configuration' => json_encode([]),
                'affiliate_commission' => 0,

                // Chatbot Features (new)
                'max_chatbots' => 10,
                'max_messages_per_month' => 50000,
                'max_agents_per_chatbot' => 5,
                'training_data_size_mb' => 100,
                'chatbot_voice_enabled' => true,
                'chatbot_image_enabled' => true,
                'chatbot_human_takeover_enabled' => true,
                'chatbot_analytics_enabled' => true,
            ]
        );

        // Update existing package if needed
        if (!$package->wasRecentlyCreated) {
            $package->update([
                'max_chatbots' => 10,
                'max_messages_per_month' => 50000,
                'max_agents_per_chatbot' => 5,
                'training_data_size_mb' => 100,
                'chatbot_voice_enabled' => true,
                'chatbot_image_enabled' => true,
                'chatbot_human_takeover_enabled' => true,
                'chatbot_analytics_enabled' => true,
            ]);
        }

        $this->command->info('✓ Test Package Created/Updated');
        $this->command->info('  Name: Chatbot Test Package');
        $this->command->info('  Features: All chatbot features enabled');
        $this->command->line('');

        // Create Active Subscription for Test User
        $subscription = Subscription::firstOrCreate(
            [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'status' => 'running'
            ],
            [
                'uid' => Str::uuid(),
                'user_id' => $user->id,
                'package_id' => $package->id,
                'status' => 'running',
                'payment_status' => 'complete',

                // Word balance
                'word_balance' => 100000,
                'remaining_word_balance' => 100000,
                'carried_word_balance' => 0,

                // Image balance
                'image_balance' => 1000,
                'remaining_image_balance' => 1000,
                'carried_image_balance' => 0,

                // Video balance
                'video_balance' => 100,
                'remaining_video_balance' => 100,
                'carried_video_balance' => 0,

                // Social features
                'total_profile' => 10,
                'carried_profile' => 0,
                'post_balance' => 1000,
                'remaining_post_balance' => 1000,
                'carried_post_balance' => 0,

                // Payment
                'payment_amount' => 0.00,
                'trx_code' => 'TEST-' . strtoupper(Str::random(10)),

                // Dates
                'expired_at' => now()->addYear(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->command->info('✓ Test Subscription Created');
        $this->command->info('  Status: Active (Running)');
        $this->command->info('  Expires: ' . $subscription->expired_at->format('Y-m-d'));
        $this->command->line('');

        $this->command->info('═══════════════════════════════════════════════════');
        $this->command->info('TEST ACCOUNTS READY!');
        $this->command->info('═══════════════════════════════════════════════════');
        $this->command->line('');
        $this->command->info('ADMIN LOGIN:');
        $this->command->info('  URL: /admin/login');
        $this->command->info('  Email: admin@test.com');
        $this->command->info('  Password: password123');
        $this->command->line('');
        $this->command->info('USER LOGIN:');
        $this->command->info('  URL: /login');
        $this->command->info('  Email: user@test.com');
        $this->command->info('  Password: password123');
        $this->command->line('');
        $this->command->info('USER HAS ACTIVE SUBSCRIPTION WITH:');
        $this->command->info('  ✓ 10 Chatbots allowed');
        $this->command->info('  ✓ 50,000 messages/month');
        $this->command->info('  ✓ 5 agents per chatbot');
        $this->command->info('  ✓ 100MB training data');
        $this->command->info('  ✓ Voice, Image, Human Takeover enabled');
        $this->command->info('  ✓ Analytics enabled');
        $this->command->line('');
        $this->command->info('TO TEST CHATBOT SYSTEM:');
        $this->command->info('  1. Login as user@test.com');
        $this->command->info('  2. Go to /user/chatbot/list');
        $this->command->info('  3. Add your API keys at /user/api-keys');
        $this->command->info('  4. Create a chatbot and start testing!');
        $this->command->line('');
    }
}
