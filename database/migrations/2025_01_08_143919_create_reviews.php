<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tạo bảng mới.
     */
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->increments('review_id'); 
            $table->unsignedInteger('product_id'); 
            $table->unsignedInteger('user_id'); 
            $table->integer('rating'); 
            $table->text('comment')->nullable(); 
            $table->text('response')->nullable(); 
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP')); 
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')); 
            $table->unsignedInteger('likes')->default(0); 
            $table->unsignedInteger('dislikes')->default(0); 
            $table->softDeletes();
        });
    }

    /**
     * Xóa bảng.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};