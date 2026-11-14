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
        if (!Schema::hasTable('order_delivery_logs')) {
            Schema::create('order_delivery_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->index();
                $table->unsignedInteger('user_id')->nullable()->index();
                $table->string('from_delivery_status', 50)->nullable();
                $table->string('to_delivery_status', 50);
                $table->unsignedInteger('from_order_status')->nullable();
                $table->unsignedInteger('to_order_status');
                $table->string('shipper_name', 255)->nullable();
                $table->string('shipper_phone', 50)->nullable();
                $table->text('note')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_delivery_logs');
    }
};
