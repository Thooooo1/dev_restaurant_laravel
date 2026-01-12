<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('order', 'payment_method')) {
            Schema::table('order', function (Blueprint $table) {
                // 1: tiền mặt, 2: chuyển khoản NH. Default 1.
                $table->tinyInteger('payment_method')->default(1)->comment('1: Tien mat, 2: Chuyen khoan NH')->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('order', 'payment_method')) {
            Schema::table('order', function (Blueprint $table) {
                $table->dropColumn('payment_method');
            });
        }
    }
};
