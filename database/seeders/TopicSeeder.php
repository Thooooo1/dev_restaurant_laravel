<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TopicSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('topic')->insert([
            [
                'id' => 1,
                'name' => 'Tin tức Ẩm thực',
                'slug' => 'tin-tuc-am-thuc',
                'description' => 'Tin tức cập nhật về ẩm thực và xu hướng mới',
                'sort_order' => 1,
                'created_by' => 1,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Khuyến mãi & Sự kiện',
                'slug' => 'khuyen-mai-su-kien',
                'description' => 'Chương trình ưu đãi hấp dẫn dành cho khách hàng',
                'sort_order' => 2,
                'created_by' => 1,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Bí quyết Nấu ăn',
                'slug' => 'bi-quyet-nau-an',
                'description' => 'Chia sẻ công thức và mẹo chế biến món ăn ngon',
                'sort_order' => 3,
                'created_by' => 1,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
