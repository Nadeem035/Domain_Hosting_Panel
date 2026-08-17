<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('panels') && ! Schema::hasColumn('panels', 'host')) {
            Schema::table('panels', function (Blueprint $table) {
                $table->string('host')->nullable()->after('type');
                $table->string('ip_address', 45)->nullable()->after('host');
                $table->unsignedInteger('client_limit')->default(0)->after('ip_address');
                $table->string('username', 100)->nullable()->after('client_limit');
                $table->boolean('is_active')->default(true)->after('login_url');
            });
        }

        if (Schema::hasTable('panels')) {
            DB::table('panels')->update([
                'type' => DB::raw("CASE type WHEN 'hosting' THEN 'cpanel' WHEN 'domain' THEN 'other' WHEN 'both' THEN 'other' ELSE type END"),
            ]);

            DB::statement("ALTER TABLE panels MODIFY type ENUM('cpanel','whm','plesk','directadmin','other') NOT NULL DEFAULT 'cpanel'");
        }

        if (Schema::hasTable('hosting_plans') && ! Schema::hasColumn('hosting_plans', 'billing_cycle')) {
            Schema::table('hosting_plans', function (Blueprint $table) {
                $table->enum('billing_cycle', ['monthly', 'quarterly', 'semi_annual', 'annual', 'biennial', 'triennial'])->default('monthly')->after('name');
                $table->decimal('price', 10, 2)->default(0)->after('billing_cycle');
                $table->json('features')->nullable()->after('bandwidth');
                $table->string('description')->nullable()->after('features');
                $table->boolean('is_active')->default(true)->after('description');
            });
        }
    }

    public function down(): void
    {
        Schema::table('panels', function (Blueprint $table) {
            $table->dropColumn(['host', 'ip_address', 'client_limit', 'username', 'is_active']);
        });

        Schema::table('hosting_plans', function (Blueprint $table) {
            $table->dropColumn(['billing_cycle', 'price', 'features', 'description', 'is_active']);
        });
    }
};