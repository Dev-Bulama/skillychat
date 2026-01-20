<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::table('themes')->insert([
            [
                'name' => 'SkillChat Professional',
                'slug' => 'skillchat-pro',
                'description' => 'A modern, clean, and professional theme for SkillChat with responsive design and beautiful UI components.',
                'version' => '1.0.0',
                'author' => 'SkillChat Team',
                'author_url' => 'https://skillchat.io',
                'preview_image' => null,
                'screenshots' => json_encode([]),
                'config' => json_encode([
                    'colors' => [
                        'primary' => '#4F46E5',
                        'secondary' => '#10B981',
                        'accent' => '#F59E0B',
                        'text' => '#1F2937',
                        'background' => '#FFFFFF',
                    ],
                    'fonts' => [
                        'heading' => 'Inter, sans-serif',
                        'body' => 'Inter, sans-serif',
                    ],
                    'layout' => [
                        'header_style' => 'modern',
                        'footer_style' => 'full',
                        'sidebar_position' => 'left',
                    ],
                ]),
                'status' => 'active',
                'is_default' => true,
                'is_system' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'SkillChat Minimal',
                'slug' => 'skillchat-minimal',
                'description' => 'A minimalist theme with focus on content and simplicity. Perfect for clean, distraction-free user experience.',
                'version' => '1.0.0',
                'author' => 'SkillChat Team',
                'author_url' => 'https://skillchat.io',
                'preview_image' => null,
                'screenshots' => json_encode([]),
                'config' => json_encode([
                    'colors' => [
                        'primary' => '#000000',
                        'secondary' => '#6B7280',
                        'accent' => '#3B82F6',
                        'text' => '#374151',
                        'background' => '#F9FAFB',
                    ],
                    'fonts' => [
                        'heading' => 'Space Grotesk, sans-serif',
                        'body' => 'Inter, sans-serif',
                    ],
                    'layout' => [
                        'header_style' => 'minimal',
                        'footer_style' => 'compact',
                        'sidebar_position' => 'right',
                    ],
                ]),
                'status' => 'inactive',
                'is_default' => false,
                'is_system' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
