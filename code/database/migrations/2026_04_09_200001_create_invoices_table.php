<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Auto-generated invoice number e.g. INV-2026-0001
            $table->string('invoice_number')->unique();

            // Client details (stored on invoice for archival)
            $table->string('client_name');
            $table->string('client_email')->nullable();
            $table->string('client_phone')->nullable();
            $table->text('client_address')->nullable();

            // Company/sender details snapshot
            $table->string('company_name')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_phone')->nullable();
            $table->text('company_address')->nullable();
            $table->string('company_logo')->nullable();   // stored file path

            // Financials
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('vat_rate', 5, 2)->default(0);       // e.g. 7.50 means 7.5%
            $table->decimal('vat_amount', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('currency', 10)->default('USD');

            // Status: draft | sent | paid | overdue | cancelled
            $table->string('status')->default('draft');

            $table->text('notes')->nullable();
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->string('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);    // quantity * unit_price
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
