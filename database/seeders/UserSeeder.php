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
        DB::table('user')->insert([
            'username' => 'admin',
            'password' => Hash::make('admin123456'), // Hash mật khẩu
            'fullname' => 'Administrator',
            'gender' => 'Male', // Có thể tùy chỉnh 'Male' hoặc 'Female'
            'thumbnail' => 'path/to/thumbnail.jpg', // Thêm ảnh nếu cần
            'email' => 'admin@gmail.com',
            'phone' => '123456789',
            'address' => '123 Admin Street',
            'roles' => 'admin', // Quyền 'admin'
            'status' => 1, // Trạng thái 'active'
            'created_by' => 1, // ID của người tạo
            'updated_by' => null, // Có thể là null
            'deleted_at' => null, // Nếu có soft delete
            'google_id' => null, // Google ID nếu có
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}