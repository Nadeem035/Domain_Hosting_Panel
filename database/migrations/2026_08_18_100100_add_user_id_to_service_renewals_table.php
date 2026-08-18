<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_renewals', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        DB::table('service_renewals')
            ->whereNull('user_id')
            ->update(['user_id' => DB::raw('(SELECT user_id FROM services WHERE services.id = service_renewals.service_id)')]);
    }

    public function down(): void
    {
        Schema::table('service_renewals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
