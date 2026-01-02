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
        Schema::table('order', function (Blueprint $table) {
            if (!Schema::hasColumn('order', 'orderStyle')) {
                $table->unsignedTinyInteger('orderStyle')->nullable()->after('status')->comment('1: Khách tự order, 2: Nhân viên order');
            }
            if (!Schema::hasColumn('order', 'table_id')) {
                $table->unsignedBigInteger('table_id')->nullable()->after('orderStyle');
                $table->foreign('table_id')->references('id')->on('tables')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order', function (Blueprint $table) {
            if (Schema::hasColumn('order', 'table_id')) {
                $table->dropForeign(['table_id']);
                $table->dropColumn('table_id');
            }
            if (Schema::hasColumn('order', 'orderStyle')) {
                $table->dropColumn('orderStyle');
            }
        });
    }
};
