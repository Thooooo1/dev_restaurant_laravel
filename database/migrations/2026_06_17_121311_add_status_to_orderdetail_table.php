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
        Schema::table('orderdetail', function (Blueprint $table) {
            $table->string('status')->default('pending')->comment('pending: Chờ xác nhận, confirmed: Đã xác nhận, cooking: Đầu bếp đang chế biến');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orderdetail', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
