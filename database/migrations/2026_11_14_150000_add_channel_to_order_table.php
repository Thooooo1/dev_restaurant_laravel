<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('order', 'channel')) {
            Schema::table('order', function (Blueprint $table) {
                $table->string('channel', 50)->nullable()->after('orderStyle');
            });
        }

        // Điền trước giá trị channel cho các đơn hàng hiện có dựa trên table_id và orderStyle
        DB::table('order')->whereNotNull('table_id')->whereNull('channel')->update(['channel' => 'dine_in']);
        DB::table('order')->whereNull('table_id')->where('orderStyle', 2)->whereNull('channel')->update(['channel' => 'pos']);
        DB::table('order')->whereNull('table_id')->where('orderStyle', 3)->whereNull('channel')->update(['channel' => 'takeaway']);
        DB::table('order')->whereNull('table_id')->where('orderStyle', 1)->whereNull('channel')->update(['channel' => 'delivery']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('order', 'channel')) {
            Schema::table('order', function (Blueprint $table) {
                $table->dropColumn('channel');
            });
        }
    }
};
