<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pharmacy_orders', function (Blueprint $table): void {
            $table->timestamp('stock_reserved_at')->nullable()->after('cancelled_at');
            $table->timestamp('stock_released_at')->nullable()->after('stock_reserved_at');
        });
    }

    public function down(): void
    {
        Schema::table('pharmacy_orders', function (Blueprint $table): void {
            $table->dropColumn(['stock_reserved_at', 'stock_released_at']);
        });
    }
};
