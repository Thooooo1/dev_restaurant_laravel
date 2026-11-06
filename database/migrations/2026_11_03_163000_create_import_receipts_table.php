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
        // 1. Tạo bảng phiếu nhập kho (import_receipts)
        if (!Schema::hasTable('import_receipts')) {
            Schema::create('import_receipts', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique(); // Mã phiếu: PNK-20261103-001
                $table->string('supplier_name', 255); // Tên nhà cung cấp
                $table->string('supplier_phone', 50)->nullable(); // SĐT nhà cung cấp
                $table->string('supplier_address', 255)->nullable(); // Địa chỉ NCC
                $table->dateTime('import_date'); // Ngày giờ nhập kho
                $table->decimal('total_amount', 15, 0)->default(0); // Tổng giá trị phiếu
                $table->text('note')->nullable(); // Ghi chú đợt nhập
                $table->string('creator_name', 100)->nullable(); // Tên người lập phiếu
                $table->unsignedBigInteger('created_by')->nullable(); // User ID người lập
                $table->unsignedTinyInteger('status')->default(1); // 1: Đã nhập kho, 0: Bản nháp
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 2. Thêm cột import_receipt_id vào bảng import_goods
        if (Schema::hasTable('import_goods') && !Schema::hasColumn('import_goods', 'import_receipt_id')) {
            Schema::table('import_goods', function (Blueprint $table) {
                $table->unsignedBigInteger('import_receipt_id')->nullable()->after('id')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('import_goods') && Schema::hasColumn('import_goods', 'import_receipt_id')) {
            Schema::table('import_goods', function (Blueprint $table) {
                $table->dropColumn('import_receipt_id');
            });
        }

        Schema::dropIfExists('import_receipts');
    }
};
