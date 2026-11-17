<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('user')->where('username', 'admin')->exists()) {
            return;
        }

        $imageCol = \Illuminate\Support\Facades\Schema::hasColumn('user', 'image') ? 'image' : 'thumbnail';

        // P1-06: Trong môi trường Production bắt buộc phải cấu hình biến môi trường SEEDER_ADMIN_PASSWORD
        $adminPassword = env('SEEDER_ADMIN_PASSWORD');
        if (app()->environment('production') && empty($adminPassword)) {
            throw new \RuntimeException('Bảo mật: Phải thiết lập biến môi trường SEEDER_ADMIN_PASSWORD trước khi chạy seeder trên môi trường Production.');
        }
        $adminPassword = $adminPassword ?: 'CivilizeAdmin@2026!';

        DB::table('user')->insert([
            'username' => 'admin',
            'password' => Hash::make($adminPassword), // Hash mật khẩu an toàn
            'fullname' => 'Administrator',
            'gender' => 'Male',
            $imageCol => 'images/users/default.png',
            'email' => 'admin@civilize.vn',
            'phone' => '0988123456',
            'address' => 'Cụm 1, TT. Phúc Thọ, Huyện Phúc Thọ, TP. Hà Nội',
            'roles' => 'admin',
            'status' => 1,
            'created_by' => 1,
            'updated_by' => null,
            'deleted_at' => null,
            'google_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}