<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Thêm cột payment_status vào bảng order nếu chưa có
        if (!Schema::hasColumn('order', 'payment_status')) {
            Schema::table('order', function (Blueprint $table) {
                $table->string('payment_status', 50)->default('pending')->after('payment_method')->index();
            });

            // Backfill đồng bộ dữ liệu cho các đơn đã thanh toán trước đó
            try {
                DB::table('order')->where('status', 2)->update(['payment_status' => 'paid']);
                DB::table('order')
                    ->where('status', 1)
                    ->whereIn('payment_method', ['VietQR', '2', 'Momo', 'momo'])
                    ->update(['payment_status' => 'paid']);
            } catch (\Throwable $e) {}
        }

        // 2. Tạo bảng payment_transactions lưu nhật ký giao dịch ngân hàng & chống webhook trùng lặp (Idempotency)
        if (!Schema::hasTable('payment_transactions')) {
            Schema::create('payment_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->nullable()->index();
                $table->string('reference_code', 100)->unique();
                $table->decimal('amount', 15, 0);
                $table->string('content', 500)->nullable();
                $table->string('gateway', 50)->default('vietqr');
                $table->string('status', 50)->default('success');
                $table->text('raw_payload')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('order', 'payment_status')) {
            Schema::table('order', function (Blueprint $table) {
                $table->dropColumn('payment_status');
            });
        }

        Schema::dropIfExists('payment_transactions');
    }
};
