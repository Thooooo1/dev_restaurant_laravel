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
            'password' => Hash::make(env('SEEDER_ADMIN_PASSWORD', 'CivilizeAdmin@2026!')), // Hash mật khẩu mạnh
            'fullname' => 'Administrator',
            'gender' => 'Male',
            'thumbnail' => 'images/users/default.png',
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