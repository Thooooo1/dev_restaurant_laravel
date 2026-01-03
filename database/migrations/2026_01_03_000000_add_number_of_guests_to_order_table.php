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
        if (!Schema::hasColumn('order', 'number_of_guests')) {
            Schema::table('order', function (Blueprint $table) {
                $table->integer('number_of_guests')->nullable()->after('phone');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('order', 'number_of_guests')) {
            Schema::table('order', function (Blueprint $table) {
                $table->dropColumn('number_of_guests');
            });
        }
    }
};
