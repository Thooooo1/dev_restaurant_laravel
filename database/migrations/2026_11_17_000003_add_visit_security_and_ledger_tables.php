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
        // 1. Visit-based token security on tables
        if (Schema::hasTable('tables')) {
            Schema::table('tables', function (Blueprint $table) {
                if (!Schema::hasColumn('tables', 'visit_token')) {
                    $table->string('visit_token', 64)->nullable()->after('qr_token');
                }
                if (!Schema::hasColumn('tables', 'visit_started_at')) {
                    $table->timestamp('visit_started_at')->nullable()->after('visit_token');
                }
            });
        }

        // 2. Inventory movements ledger
        if (!Schema::hasTable('inventory_movements')) {
            Schema::create('inventory_movements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->integer('delta'); // Negative for deductions, positive for restocks/imports
                $table->string('reason', 50); // pos_sale, table_order_placed, order_cancelled, order_refunded, import_goods, etc.
                $table->unsignedBigInteger('order_id')->nullable()->index();
                $table->unsignedBigInteger('order_detail_id')->nullable()->index();
                $table->unsignedBigInteger('import_receipt_id')->nullable()->index();
                $table->string('idempotency_key', 128)->unique();
                $table->unsignedBigInteger('actor_id')->nullable()->index();
                $table->timestamps();
            });
        }

        // 3. Payment intents
        if (!Schema::hasTable('payment_intents')) {
            Schema::create('payment_intents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->index();
                $table->string('gateway', 32); // momo, vietqr, cash, etc.
                $table->string('merchant_id', 64)->nullable();
                $table->string('request_id', 128)->index();
                $table->decimal('expected_amount', 15, 2);
                $table->string('currency', 10)->default('VND');
                $table->string('status', 32)->default('pending'); // pending, successful, failed, expired
                $table->string('gateway_transaction_id', 128)->nullable()->unique();
                $table->timestamp('expires_at')->nullable();
                $table->json('payload')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_intents');
        Schema::dropIfExists('inventory_movements');

        if (Schema::hasTable('tables')) {
            Schema::table('tables', function (Blueprint $table) {
                if (Schema::hasColumn('tables', 'visit_started_at')) {
                    $table->dropColumn('visit_started_at');
                }
                if (Schema::hasColumn('tables', 'visit_token')) {
                    $table->dropColumn('visit_token');
                }
            });
        }
    }
};
