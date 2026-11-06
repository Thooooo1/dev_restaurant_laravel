<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FloorSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('floors')->insert([
            ['id' => 1, 'name' => 'Tầng 1 - Sảnh chính', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Tầng 2 - Phòng VIP', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Tầng 3 - Ngoài trời', 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
