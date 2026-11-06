<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('banner')->insert([
            [
                'name' => 'Khuyến mãi đặc biệt mùa hè',
                'link' => '/thuc-don',
                'image' => 'banner_1.jpg',
                'position' => 'Slideshow',
                'description' => 'Giảm 20% cho tất cả các món ăn chính',
                'sort_order' => 1,
                'created_by' => 1,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Thực đơn nướng cao cấp',
                'link' => '/thuc-don',
                'image' => 'banner_2.jpg',
                'position' => 'Slideshow',
                'description' => 'Trải nghiệm hương vị độc đáo từ các món nướng',
                'sort_order' => 2,
                'created_by' => 1,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Đặt bàn ngay nhận ưu đãi',
                'link' => '/dat-ban',
                'image' => 'banner_ads.jpg',
                'position' => 'ads',
                'description' => 'Đặt bàn trước giảm ngay 10% trên tổng hóa đơn',
                'sort_order' => 1,
                'created_by' => 1,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
