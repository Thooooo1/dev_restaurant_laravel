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
        // 1. Short-Term Memory: Lưu vết hội thoại nhiều lượt (Multi-turn) theo phiên
        Schema::create('chatbot_messages', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 100)->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->enum('role', ['user', 'assistant', 'system', 'tool'])->index();
            $table->text('content')->nullable();
            $table->json('tool_calls')->nullable();   // Lưu lệnh gọi tool của AI (nếu có)
            $table->json('tool_output')->nullable();  // Dữ liệu từ MySQL trả cho AI
            $table->json('metadata')->nullable();     // Metadata phụ (thực thể trích xuất, IP, bàn ăn...)
            $table->timestamps();
        });

        // 2. Long-Term Memory: Lưu hồ sơ & thói quen ẩm thực của khách (bảo mật, không lưu chat rác)
        Schema::create('customer_ai_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->nullable()->unique()->index();
            $table->unsignedBigInteger('user_id')->nullable()->unique()->index();
            $table->string('customer_name', 100)->nullable();
            $table->json('taste_preferences')->nullable();    // vd: ["thích ăn nhạt", "uống bia tiger"]
            $table->json('dietary_restrictions')->nullable(); // vd: ["dị ứng tôm", "không ăn cay"]
            $table->string('favorite_seating', 100)->nullable(); // vd: "phòng VIP", "ngoài trời thoáng mát"
            $table->unsignedInteger('total_visits')->default(1);
            $table->text('internal_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_messages');
        Schema::dropIfExists('customer_ai_profiles');
    }
};
