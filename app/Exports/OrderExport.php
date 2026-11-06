<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Contracts\View\View;

class OrderExport
{
    protected $status;

    public function __construct($status = null)
    {
        $this->status = $status;
    }

    public function view(): View
    {
        $query = Order::query()
            ->whereNull('deleted_at')
            ->with(['table.floor', 'user', 'orderDetails.product']);

        if ($this->status !== null && $this->status != 5 && $this->status !== '') {
            $query->where('status', (int)$this->status);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $rows = [];
        $totalAmount = 0;

        foreach ($orders as $order) {
            $orderStyle = 'Tại bàn';
            if ($order->orderStyle == 2) {
                $orderStyle = 'Giao hàng';
            } elseif ($order->orderStyle == 3) {
                $orderStyle = 'Mang đi';
            }

            $tableName = $order->table ? ($order->table->name . ($order->table->floor ? ' (' . $order->table->floor->name . ')' : '')) : 'Đơn giao / Mang đi';

            $pm = $order->payment_method;
            $pmText = 'Chưa thanh toán';
            if ($pm == '1' || $pm == 'cash') {
                $pmText = 'Tiền mặt';
            } elseif ($pm == '2' || $pm == 'vietqr' || $pm == 'bank_transfer') {
                $pmText = 'Chuyển khoản VietQR';
            } elseif (!empty($pm)) {
                $pmText = $pm;
            }

            $statusText = 'Đang chờ xử lý';
            switch ($order->status) {
                case 0:
                    $statusText = 'Chờ xử lý / Đang phục vụ';
                    break;
                case 1:
                    $statusText = 'Đang vận chuyển / Đã thanh toán';
                    break;
                case 2:
                    $statusText = 'Hoàn thành';
                    break;
                case 3:
                    $statusText = 'Đã hủy';
                    break;
                case 4:
                    $statusText = 'Đã trả hàng';
                    break;
            }

            $itemsCount = $order->orderDetails->sum('qty');
            $price = (int)$order->total_price;
            $totalAmount += $price;

            $rows[] = [
                'id' => $order->id,
                'created_at' => $order->created_at ? $order->created_at->format('d/m/Y H:i') : '',
                'order_style' => $orderStyle,
                'table_name' => $tableName,
                'customer_name' => $order->customer_name ?: ($order->name ?: 'Khách vãng lai'),
                'customer_phone' => $order->customer_phone ?: ($order->phone ?: ''),
                'customer_address' => $order->address ?: 'Tại quầy',
                'items_count' => $itemsCount,
                'total_price' => $price,
                'payment_method' => $pmText,
                'status' => $statusText,
                'note' => $order->note ?: ''
            ];
        }

        return view('backend.order.export', [
            'rows' => $rows,
            'totalAmount' => $totalAmount,
            'totalOrders' => count($rows),
            'statusFilter' => $this->status
        ]);
    }
}
