<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('category')->insert([
            [
                'name' => 'Khai vị',
                'slug' => 'khai-vi',
                'parent_id' => 0,
                'sort_order' => 1,
                'image' => 'khaivi.jpg',
                'description' => 'Món ăn của khai vị',
                'created_by' => 1,
                'updated_by' => null,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Món chính',
                'slug' => 'mon-chinh',
                'parent_id' => 0,
                'sort_order' => 2,
                'image' => 'monchinh.jpg',
                'description' => 'Món ăn chính',
                'created_by' => 1,
                'updated_by' => null,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Canh - Tiềm - Súp',
                'slug' => 'canh-tiem-sup',
                'parent_id' => 1, 
                'sort_order' => 3,
                'image' => 'canhtiemsup.jpg',
                'description' => 'Món ăn của Canh - Tiềm - Súp',
                'created_by' => 1,
                'updated_by' => null,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cơm - Mì - Cháo',
                'slug' => 'com-mi-chao',
                'parent_id' => 1, 
                'sort_order' => 4,
                'image' => 'commichao.jpg',
                'description' => 'Món ăn của Cơm - Mì - Cháo',
                'created_by' => 1,
                'updated_by' => null,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bánh trang miệng',
                'slug' => 'banh-trang-mieng',
                'parent_id' => 1, 
                'sort_order' => 5,
                'image' => 'banhtrangmieng.jpg',
                'description' => 'Món ăn của Bánh tráng miệng',
                'created_by' => 1,
                'updated_by' => null,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Đồ uống',
                'slug' => 'do-uong',
                'parent_id' => 1, 
                'sort_order' => 6,
                'image' => 'douong.jpg',
                'description' => 'Đồ uống',
                'created_by' => 1,
                'updated_by' => null,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
