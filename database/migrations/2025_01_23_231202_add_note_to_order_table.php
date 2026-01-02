<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('order', 'note')) {
            Schema::table('order', function (Blueprint $table) {
                $table->string('note', 1000)->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('order', 'note')) {
            Schema::table('order', function (Blueprint $table) {
                $table->dropColumn('note');
            });
        }
    }
};
