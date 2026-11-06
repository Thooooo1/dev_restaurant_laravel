<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class UpdateProductFoodCostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();
        foreach ($products as $p) {
            if ($p->price_sale > 0) {
                // Tỷ lệ giá vốn chuẩn F&B: ~35% - 40% giá bán
                $p->price_buy = round(($p->price_sale * 0.38) / 1000) * 1000;
                $p->save();
            }
        }
    }
}
