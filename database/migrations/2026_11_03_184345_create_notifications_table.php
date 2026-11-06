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
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index(); // null = theo role
                $table->string('role', 50)->nullable()->index(); // 'admin', 'employee', 'customer', 'all'
                $table->string('type', 50)->index(); // 'booking', 'order', 'service_call', 'payment', 'system'
                $table->string('title');
                $table->text('message');
                $table->string('url')->nullable();
                $table->string('icon', 100)->default('fa-bell');
                $table->string('color', 50)->default('#F47716');
                $table->json('data')->nullable();
                $table->boolean('is_read')->default(false)->index();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
