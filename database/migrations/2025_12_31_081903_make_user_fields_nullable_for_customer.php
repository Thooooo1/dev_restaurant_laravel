<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user', function (Blueprint $table) {
            // Đổi tên cột thumbnail thành image nếu tồn tại
            if (Schema::hasColumn('user', 'thumbnail') && !Schema::hasColumn('user', 'image')) {
                // Sử dụng DB raw query để đổi tên cột (an toàn hơn với mọi database)
                \DB::statement('ALTER TABLE `user` CHANGE `thumbnail` `image` VARCHAR(1000) NULL');
            }
            
            // Cho phép nullable cho các cột cần thiết
            if (Schema::hasColumn('user', 'username')) {
                $table->string('username', 255)->nullable()->change();
            }
            
            if (Schema::hasColumn('user', 'password')) {
                $table->string('password', 255)->nullable()->change();
            }
            
            if (Schema::hasColumn('user', 'email')) {
                $table->string('email', 1000)->nullable()->change();
            }
            
            // Xử lý image (có thể là thumbnail hoặc image)
            if (Schema::hasColumn('user', 'image')) {
                $table->string('image', 1000)->nullable()->change();
            } elseif (Schema::hasColumn('user', 'thumbnail')) {
                $table->string('thumbnail', 1000)->nullable()->change();
            }
            
            // Cho phép nullable cho address
            if (Schema::hasColumn('user', 'address')) {
                $table->string('address', 1000)->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            // Khôi phục lại NOT NULL (cần cẩn thận với dữ liệu hiện có)
            if (Schema::hasColumn('user', 'username')) {
                $table->string('username', 255)->nullable(false)->change();
            }
            
            if (Schema::hasColumn('user', 'password')) {
                $table->string('password', 255)->nullable(false)->change();
            }
            
            if (Schema::hasColumn('user', 'email')) {
                $table->string('email', 1000)->nullable(false)->change();
            }
            
            if (Schema::hasColumn('user', 'image')) {
                $table->string('image', 1000)->nullable(false)->change();
            } elseif (Schema::hasColumn('user', 'thumbnail')) {
                $table->string('thumbnail', 1000)->nullable(false)->change();
            }
            
            if (Schema::hasColumn('user', 'address')) {
                $table->string('address', 1000)->nullable(false)->change();
            }
        });
    }
};
