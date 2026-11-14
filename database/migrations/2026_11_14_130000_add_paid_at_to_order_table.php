<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('order', 'paid_at')) {
            Schema::table('order', function (Blueprint $table) {
                $table->timestamp('paid_at')->nullable()->after('payment_status')->index();
            });

            // Backfill paid_at cho cac don da thanh toan truoc do
            try {
                DB::table('order')
                    ->where('payment_status', 'paid')
                    ->orWhere('status', 2)
                    ->update(['paid_at' => DB::raw('COALESCE(delivered_at, updated_at, created_at)')]);
            } catch (\Throwable $e) {}
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('order', 'paid_at')) {
            Schema::table('order', function (Blueprint $table) {
                $table->dropColumn('paid_at');
            });
        }
    }
};
