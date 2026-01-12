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
        Schema::create('import_goods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên hàng hoá (bắt buộc)
            $table->string('type')->nullable(); // Loại hàng hoá (k bắt buộc)
            $table->string('unit')->nullable(); // Đơn vị tính (cái, kg, g, l, ml, ...)
            $table->integer('quantity')->default(0); // Số lượng
            $table->decimal('price', 15, 0)->default(0); // Đơn giá
            $table->decimal('total_amount', 15, 0)->default(0); // Thành tiền
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_goods');
    }
};
