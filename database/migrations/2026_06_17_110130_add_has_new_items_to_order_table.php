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
        if (!Schema::hasColumn('order', 'has_new_items')) {
            Schema::table('order', function (Blueprint $table) {
                $table->boolean('has_new_items')->default(false)->after('order_turn');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('order', 'has_new_items')) {
            Schema::table('order', function (Blueprint $table) {
                $table->dropColumn('has_new_items');
            });
        }
    }
};
