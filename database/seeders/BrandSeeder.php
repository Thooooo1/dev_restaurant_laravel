<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('brand')->insert([
            [
                'name' => 'Nhà hàng Ẩm thực Việt',
                'slug' => 'am-thuc-viet',
                'image' => 'brand_viet.png',
                'description' => 'Thương hiệu ẩm thực truyền thống Việt Nam',
                'sort_order' => 1,
                'created_by' => 1,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Nhà hàng BBQ Hàn Quốc',
                'slug' => 'bbq-han-quoc',
                'image' => 'brand_bbq.png',
                'description' => 'Chuyên các món nướng chuẩn vị Hàn Quốc',
                'sort_order' => 2,
                'created_by' => 1,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Hải Sản Biển Đông',
                'slug' => 'hai-san-bien-dong',
                'image' => 'brand_haisan.png',
                'description' => 'Hải sản tươi sống nhập trực tiếp mỗi ngày',
                'sort_order' => 3,
                'created_by' => 1,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Lẩu Nấm Thiên Nhiên',
                'slug' => 'lau-nam',
                'image' => 'brand_nam.png',
                'description' => 'Lẩu nấm thanh ngọt bổ dưỡng cho sức khỏe',
                'sort_order' => 4,
                'created_by' => 1,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
