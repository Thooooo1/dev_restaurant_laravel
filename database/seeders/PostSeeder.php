<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('post')->insert([
            [
                'topic_id' => 1,
                'title' => 'Top 10 món ăn không thể bỏ qua khi đến nhà hàng',
                'slug' => 'top-10-mon-an-khong-the-bo-qua',
                'content' => 'Nhà hàng chúng tôi tự hào mang đến những hương vị đặc sắc nhất được chế biến bởi các đầu bếp hàng đầu...',
                'description' => 'Khám phá 10 món ăn được yêu thích nhất tại nhà hàng.',
                'thumbnail' => 'post_1.jpg',
                'type' => 'post',
                'created_by' => 1,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'topic_id' => 2,
                'title' => 'Chương trình tri ân khách hàng thân thiết tháng này',
                'slug' => 'chuong-trinh-tri-an-khach-hang',
                'content' => 'Nhằm tri ân sự ủng hộ của quý khách hàng, nhà hàng áp dụng ưu đãi giảm giá lên đến 30% cho khách hàng đặt bàn online...',
                'description' => 'Ưu đãi cực lớn dành cho khách hàng đặt bàn trực tuyến.',
                'thumbnail' => 'post_2.jpg',
                'type' => 'post',
                'created_by' => 1,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'topic_id' => 3,
                'title' => 'Bí quyết ướp thịt nướng đậm đà chuẩn vị nhà hàng',
                'slug' => 'bi-quyet-uop-thit-nuong-dam-da',
                'content' => 'Bí kíp tạo nên độ mềm ngọt và hương vị đậm đà cho món thịt nướng chính là ở khâu lựa chọn gia vị...',
                'description' => 'Hướng dẫn chi tiết cách ướp thịt ngon như đầu bếp chuyên nghiệp.',
                'thumbnail' => 'post_3.jpg',
                'type' => 'post',
                'created_by' => 1,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
