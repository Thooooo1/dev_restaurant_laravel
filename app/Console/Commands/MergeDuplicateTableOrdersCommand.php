<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TableOrderService;
use App\Models\Table;

class MergeDuplicateTableOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:merge-duplicate-table-orders {--apply : Thực thi gộp đơn vào cơ sở dữ liệu}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Quét và gộp các đơn hàng đang hoạt động bị trùng lặp trên cùng một bàn';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $apply = (bool)$this->option('apply');

        $this->info($apply ? 'Bắt đầu gộp các đơn hàng trùng lặp trên từng bàn...' : 'Chế độ xem trước (Dry-run): Quét các bàn có nhiều đơn đang hoạt động...');

        $results = TableOrderService::mergeAllDuplicateTableOrders($apply);

        if (empty($results)) {
            $this->info('Không tìm thấy bàn nào có đơn hàng trùng lặp.');
            return Command::SUCCESS;
        }

        $rows = [];
        foreach ($results as $res) {
            $table = Table::find($res['table_id']);
            $tableName = $table ? $table->name : "Bàn #{$res['table_id']}";

            $rows[] = [
                'table_id' => $res['table_id'],
                'table_name' => $tableName,
                'primary_order_id' => '#' . $res['primary_order_id'],
                'duplicate_orders' => implode(', ', array_map(fn($id) => '#' . $id, $res['duplicate_order_ids'])),
                'duplicates_count' => $res['duplicates_count'],
                'status' => $apply ? 'Đã gộp' : 'Cần gộp',
            ];
        }

        $this->table(['ID Bàn', 'Tên Bàn', 'Đơn Chính (Giữ lại)', 'Đơn Trùng (Gộp & Hủy)', 'Số lượng trùng', 'Trạng thái'], $rows);

        if (!$apply) {
            $this->warn('Đây là chế độ xem trước (Dry-run). Dữ liệu chưa thay đổi.');
            $this->comment('Chạy lại lệnh với cờ --apply để thực thi: php artisan orders:merge-duplicate-table-orders --apply');
        } else {
            $this->info('Đã gộp thành công ' . count($results) . ' bàn có đơn trùng lặp!');
        }

        return Command::SUCCESS;
    }
}
