<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('panel_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('hosting_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['domain', 'hosting', 'both'])->default('domain');
            $table->string('domain_name')->nullable();
            $table->date('created_date');
            $table->date('expiry_date');
            $table->date('client_reminder_date')->nullable();
            $table->decimal('company_price', 10, 2)->default(0);
            $table->decimal('client_price', 10, 2)->default(0);
            $table->string('currency', 8)->default('USD');
            $table->enum('status', ['active', 'expired', 'cancelled', 'pending_renewal'])->default('active');
            $table->boolean('auto_renew_tracking')->default(true);
            $table->string('last_expiry_tier')->nullable();
            $table->string('last_payment_tier')->nullable();
            $table->timestamp('last_notified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index('expiry_date');
            $table->index('client_reminder_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};