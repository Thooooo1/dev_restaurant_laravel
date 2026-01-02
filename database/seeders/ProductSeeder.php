<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 50; $i++) {
            DB::table('product')->insert([
                'category_id' => rand(1, 6), 
                'brand_id' => rand(1, 4),    
                'name' => "Món ăn $i",
                'slug' => "mon-an-$i",
                'content' => "Đây là nội dung của Món ăn $i",
                'description' => "Mô tả ngắn gọn về Món ăn $i",
                'price_buy' => rand(50000, 100000), 
                'price_sale' => rand(20000, 50000), 
                'qty' => rand(1, 100), 
                'thumbnail' => "anh_monan_($i).jpg",
                'created_by' => 1, 
                'updated_by' => null,
                'new_p' => rand(0, 1), 
                'status' => 1, 
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
