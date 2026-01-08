<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orderdetail', function (Blueprint $table) {
            $table->unsignedInteger('order_turn')->default(1)->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('orderdetail', function (Blueprint $table) {
            $table->dropColumn('order_turn');
        });
    }
};

