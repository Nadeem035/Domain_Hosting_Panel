<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_renewals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->date('renewed_on');
            $table->date('previous_expiry_date');
            $table->date('new_expiry_date');
            $table->decimal('company_price', 10, 2)->default(0);
            $table->decimal('client_price', 10, 2)->default(0);
            $table->boolean('payment_received')->default(false);
            $table->date('payment_received_date')->nullable();
            $table->string('invoice_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['service_id', 'renewed_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_renewals');
    }
};