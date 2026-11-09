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
        Schema::table('order', function (Blueprint $table) {
            if (!Schema::hasColumn('order', 'shipper_name')) {
                $table->string('shipper_name', 255)->nullable()->after('address');
            }
            if (!Schema::hasColumn('order', 'shipper_phone')) {
                $table->string('shipper_phone', 50)->nullable()->after('shipper_name');
            }
            if (!Schema::hasColumn('order', 'delivery_status')) {
                $table->string('delivery_status', 50)->default('waiting')->after('shipper_phone');
            }
            if (!Schema::hasColumn('order', 'picked_up_at')) {
                $table->timestamp('picked_up_at')->nullable()->after('delivery_status');
            }
            if (!Schema::hasColumn('order', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('picked_up_at');
            }
            if (!Schema::hasColumn('order', 'delivery_note')) {
                $table->text('delivery_note')->nullable()->after('delivered_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order', function (Blueprint $table) {
            $columns = ['shipper_name', 'shipper_phone', 'delivery_status', 'picked_up_at', 'delivered_at', 'delivery_note'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('order', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
