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
        Schema::create('service_calls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('table_id')->nullable()->index();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->string('customer_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('call_type')->default('call_waiter'); // call_waiter, water, cutlery, bill, clean, other
            $table->string('call_type_label')->nullable();
            $table->text('note')->nullable();
            $table->tinyInteger('status')->default(0); // 0: Chờ phục vụ, 1: Đang phục vụ, 2: Đã xong, 3: Đã hủy
            $table->unsignedBigInteger('handled_by')->nullable();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_calls');
    }
};
