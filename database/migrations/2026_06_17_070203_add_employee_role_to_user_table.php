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
        Schema::table('user', function (Blueprint $table) {
            // Thay đổi cột roles từ enum thành string để dễ mở rộng
            $table->string('roles', 20)->default('customer')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            // Trở về enum cũ nếu cần rollback
            $table->enum('roles', ['admin', 'customer'])->default('customer')->change();
        });
    }
};
