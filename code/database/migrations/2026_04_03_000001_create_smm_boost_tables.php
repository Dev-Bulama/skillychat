<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SMM API Providers configured by admin
        Schema::create('smm_providers', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('api_url');
            $table->string('api_key');
            $table->json('extra_config')->nullable(); // reserved for future credentials
            $table->string('status')->default('1');   // StatusEnum: '1' active, '0' inactive
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        // Internal service catalog (what users see and order)
        Schema::create('smm_services', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->unsignedBigInteger('provider_id');
            $table->string('provider_service_id');   // provider's own service ID
            $table->string('name');
            $table->string('platform');              // instagram, tiktok, youtube, etc.
            $table->string('service_type');          // followers, likes, views, subscribers, etc.
            $table->text('description')->nullable();
            $table->decimal('price_per_1000', 12, 4); // price per 1000 units in base currency
            $table->unsignedInteger('min_quantity')->default(100);
            $table->unsignedInteger('max_quantity')->default(100000);
            $table->string('delivery_estimate')->nullable(); // e.g. "1-3 days"
            $table->string('status')->default('1');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('provider_id')->references('id')->on('smm_providers')->onDelete('cascade');
        });

        // User orders
        Schema::create('smm_orders', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('service_id');
            $table->string('platform');
            $table->string('service_type');
            $table->text('target_link');             // URL or username
            $table->unsignedInteger('quantity');
            $table->decimal('charge', 12, 4);        // amount deducted from user wallet
            $table->decimal('provider_cost', 12, 4)->default(0); // cost from provider side
            $table->string('currency_symbol')->default('$');
            $table->string('status')->default('pending'); // pending|processing|completed|failed|refunded
            $table->string('provider_order_id')->nullable(); // ID returned by SMM API
            $table->unsignedInteger('start_count')->default(0);
            $table->unsignedInteger('remains')->default(0);
            $table->text('remarks')->nullable();
            $table->timestamp('sent_to_provider_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('refunded_by')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('smm_services')->onDelete('restrict');
        });

        // API communication logs (request/response per order action)
        Schema::create('smm_order_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('action');                // place_order|check_status|cancel
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->boolean('success')->default(false);
            $table->string('http_status')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('smm_orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smm_order_logs');
        Schema::dropIfExists('smm_orders');
        Schema::dropIfExists('smm_services');
        Schema::dropIfExists('smm_providers');
    }
};
