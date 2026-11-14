<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('order', 'revenue_recognized_at')) {
            Schema::table('order', function (Blueprint $table) {
                $table->timestamp('revenue_recognized_at')->nullable()->after('paid_at')->index();
            });

            // Backfill revenue_recognized_at cho cac don da hoan tat truoc do
            try {
                DB::table('order')
                    ->where('delivery_status', 'delivered')
                    ->update(['revenue_recognized_at' => DB::raw('COALESCE(delivered_at, paid_at, updated_at)')]);

                DB::table('order')
                    ->whereNotNull('table_id')
                    ->where('status', 2)
                    ->update(['revenue_recognized_at' => DB::raw('COALESCE(paid_at, updated_at, created_at)')]);
            } catch (\Throwable $e) {}
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('order', 'revenue_recognized_at')) {
            Schema::table('order', function (Blueprint $table) {
                $table->dropColumn('revenue_recognized_at');
            });
        }
    }
};
