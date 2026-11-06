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
        if (!Schema::hasTable('order_item_logs')) {
            Schema::create('order_item_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->index();
                $table->unsignedBigInteger('order_detail_id')->nullable()->index();
                $table->unsignedBigInteger('product_id')->nullable()->index();
                $table->string('product_name');
                $table->string('action'); // created, quantity_changed, cancelled
                $table->integer('old_qty')->default(0);
                $table->integer('new_qty')->default(0);
                $table->text('reason')->nullable();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('kitchen_status')->nullable(); // pending, cooking, served
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_logs');
    }
};
