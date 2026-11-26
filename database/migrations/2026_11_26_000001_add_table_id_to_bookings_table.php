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
        if (!Schema::hasColumn('bookings', 'table_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->unsignedBigInteger('table_id')->nullable()->after('status');
                $table->index('table_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('bookings', 'table_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropIndex(['table_id']);
                $table->dropColumn('table_id');
            });
        }
    }
};
