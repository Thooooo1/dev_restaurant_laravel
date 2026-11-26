<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('tables')->count() > 0) {
            return;
        }

        $tables = [];
        $tableNumber = 1;

        // Tầng 1 (8 bàn: Bàn 1 -> Bàn 8)
        for ($i = 1; $i <= 8; $i++, $tableNumber++) {
            $tables[] = [
                'floor_id' => 1,
                'name' => "Bàn {$tableNumber}",
                'seats' => rand(2, 6),
                'sort_order' => $tableNumber,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Tầng 2 (6 bàn: Bàn 9 -> Bàn 14)
        for ($i = 1; $i <= 6; $i++, $tableNumber++) {
            $tables[] = [
                'floor_id' => 2,
                'name' => "Bàn {$tableNumber}",
                'seats' => rand(6, 12),
                'sort_order' => $tableNumber,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Tầng 3 (6 bàn: Bàn 15 -> Bàn 20)
        for ($i = 1; $i <= 6; $i++, $tableNumber++) {
            $tables[] = [
                'floor_id' => 3,
                'name' => "Bàn {$tableNumber}",
                'seats' => rand(2, 8),
                'sort_order' => $tableNumber,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('tables')->insert($tables);
    }
}
