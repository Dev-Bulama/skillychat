<?php

use App\Enums\NotificationTemplateEnum;
use App\Models\Admin\Template;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $slug = 'SUBSCRIPTION_REMINDER';
        $data = NotificationTemplateEnum::notificationTemplateEnum($slug);

        if ($data) {
            Template::firstOrCreate(
                ['slug' => $slug],
                array_merge($data, ['uid' => Str::uuid()->toString()])
            );
        }
    }

    public function down(): void
    {
        Template::where('slug', 'SUBSCRIPTION_REMINDER')->delete();
    }
};
