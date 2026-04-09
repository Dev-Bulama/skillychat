<?php

use App\Models\FeatureFlag;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private array $flags = [
        [
            'name'        => 'whatsapp_chatbot',
            'label'       => 'WhatsApp Chatbot',
            'description' => 'Allow users to connect WhatsApp Business API accounts and send/receive messages with AI-powered auto-replies.',
            'is_enabled'  => true,
        ],
        [
            'name'        => 'lead_crm',
            'label'       => 'Lead CRM',
            'description' => 'Lightweight CRM for managing leads through a visual Kanban pipeline. Includes stages, activities, and notes.',
            'is_enabled'  => true,
        ],
        [
            'name'        => 'drip_sequences',
            'label'       => 'Drip Sequences',
            'description' => 'Automated multi-step messaging sequences via Email, SMS, and WhatsApp triggered by lead events or manual enrollment.',
            'is_enabled'  => true,
        ],
        [
            'name'        => 'invoices',
            'label'       => 'Invoice Generator',
            'description' => 'Create, manage, and send professional invoices with auto-numbering, VAT, discounts, and email delivery.',
            'is_enabled'  => true,
        ],
    ];

    public function up(): void
    {
        foreach ($this->flags as $flag) {
            FeatureFlag::firstOrCreate(['name' => $flag['name']], $flag);
        }
    }

    public function down(): void
    {
        FeatureFlag::whereIn('name', array_column($this->flags, 'name'))->delete();
    }
};
