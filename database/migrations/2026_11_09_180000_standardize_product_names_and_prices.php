<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            DB::table('product')
                ->where('name', 'like', '%Haniken%')
                ->update(['name' => 'Bia Heineken', 'slug' => 'bia-heineken']);

            $mainDishes = [
                52 => 369000,
                53 => 359000,
                54 => 319000,
                55 => 299000,
                60 => 129000,
                62 => 85000,
                83 => 79000,
                84 => 149000,
                85 => 199000,
                86 => 169000,
            ];

            foreach ($mainDishes as $pid => $price) {
                DB::table('product')
                    ->where('id', $pid)
                    ->where('price_buy', '<', 100000)
                    ->update(['price_buy' => $price, 'price_sale' => $price]);
            }
        } catch (\Throwable $e) {
            // Khong lam loi migration neu bang chua khoi tao
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Khong can rollback du lieu chuan hoa
    }
};
