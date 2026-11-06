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
        if (Schema::hasTable('post') && !Schema::hasColumn('post', 'view')) {
            Schema::table('post', function (Blueprint $table) {
                $table->unsignedBigInteger('view')->default(0)->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('post') && Schema::hasColumn('post', 'view')) {
            Schema::table('post', function (Blueprint $table) {
                $table->dropColumn('view');
            });
        }
    }
};
