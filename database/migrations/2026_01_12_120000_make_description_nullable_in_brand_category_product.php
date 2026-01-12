<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = DB::getTablePrefix();

        if (Schema::hasTable('brand')) {
            DB::statement("ALTER TABLE `{$prefix}brand` MODIFY `description` TEXT NULL");
        }

        if (Schema::hasTable('category')) {
            DB::statement("ALTER TABLE `{$prefix}category` MODIFY `description` TEXT NULL");
        }

        if (Schema::hasTable('product')) {
            DB::statement("ALTER TABLE `{$prefix}product` MODIFY `description` TEXT NULL");
        }
    }

    public function down(): void
    {
    }
};
