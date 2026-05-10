<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('provider_staff', 'permissions')) {
            return;
        }

        Schema::table('provider_staff', function (Blueprint $table): void {
            $table->json('permissions')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('provider_staff', 'permissions')) {
            return;
        }

        Schema::table('provider_staff', function (Blueprint $table): void {
            $table->dropColumn('permissions');
        });
    }
};
