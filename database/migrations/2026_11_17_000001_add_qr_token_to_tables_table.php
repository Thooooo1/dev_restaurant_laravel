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
        if (Schema::hasTable('tables')) {
            Schema::table('tables', function (Blueprint $table) {
                if (!Schema::hasColumn('tables', 'qr_token')) {
                    $table->string('qr_token', 64)->nullable()->after('status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tables')) {
            Schema::table('tables', function (Blueprint $table) {
                if (Schema::hasColumn('tables', 'qr_token')) {
                    $table->dropColumn('qr_token');
                }
            });
        }
    }
};
