<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        $tables = [];
        
        // Tầng 1 (8 bàn)
        for ($i = 1; $i <= 8; $i++) {
            $tables[] = [
                'floor_id' => 1,
                'name' => "Bàn 1.0$i",
                'seats' => rand(2, 6),
                'sort_order' => $i,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Tầng 2 (6 bàn VIP)
        for ($i = 1; $i <= 6; $i++) {
            $tables[] = [
                'floor_id' => 2,
                'name' => "Bàn VIP 2.0$i",
                'seats' => rand(6, 12),
                'sort_order' => $i,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Tầng 3 (6 bàn sân thượng)
        for ($i = 1; $i <= 6; $i++) {
            $tables[] = [
                'floor_id' => 3,
                'name' => "Bàn Rooftop 3.0$i",
                'seats' => rand(2, 8),
                'sort_order' => $i,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('tables')->insert($tables);
    }
}
