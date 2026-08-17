<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hosting_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('panel_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'semi_annual', 'annual', 'biennial', 'triennial'])->default('monthly');
            $table->decimal('price', 10, 2)->default(0);
            $table->string('disk_space')->nullable();
            $table->string('bandwidth')->nullable();
            $table->json('features')->nullable();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'panel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hosting_plans');
    }
};