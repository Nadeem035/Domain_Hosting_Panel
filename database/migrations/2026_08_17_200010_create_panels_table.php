<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('panels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['cpanel', 'whm', 'plesk', 'directadmin', 'other'])->default('cpanel');
            $table->string('host')->nullable();
            $table->string('ip_address')->nullable();
            $table->unsignedInteger('client_limit')->default(0);
            $table->string('username')->nullable();
            $table->string('login_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('panels');
    }
};