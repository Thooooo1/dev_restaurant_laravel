<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Contracts\View\View;

class OrderExport
{
    protected $status;
    protected $onlyDelivery;

    public function __construct($status = null, $onlyDelivery = false)
    {
        $this->status = $status;
        $this->onlyDelivery = $onlyDelivery;
    }

    public function view(): View
    {
        $query = Order::query()
            ->whereNull('deleted_at')
            ->with(['table.floor', 'user', 'orderDetails.product']);

        if ($this->onlyDelivery) {
            $query->where(function ($q) {
                $q->whereNull('table_id')
                  ->orWhere('table_id', 0);
            });
        }

        if ($this->status !== null && $this->status != 5 && $this->status !== '') {
            $query->where('status', (int)$this->status);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $rows = [];
        $totalAmount = 0;

        foreach ($orders as $order) {
            $orderStyle = $order->table_id ? 'Tại bàn' : 'Giao hàng';
            if (!$order->table_id) {
                if ((int) $order->orderStyle === Order::STYLE_POS) {
                    $orderStyle = 'Tại quầy';
                } elseif ((int) $order->orderStyle === Order::STYLE_TAKEAWAY) {
                    $orderStyle = 'Mang đi';
                } elseif ((int) $order->orderStyle === Order::STYLE_DELIVERY) {
                    $orderStyle = 'Giao hàng';
                }
            }

            $tableName = $order->table ? ($order->table->name . ($order->table->floor ? ' (' . $order->table->floor->name . ')' : '')) : 'Đơn giao / Mang đi';

            $pm = (string) ($order->payment_method ?? '');
            $pmLower = strtolower(trim($pm));
            $pmText = 'Chưa thanh toán';
            if ($pm === '1' || $pmLower === 'cash' || $pmLower === 'cod') {
                $pmText = $pmLower === 'cod' ? 'Tiền mặt (COD)' : 'Tiền mặt';
            } elseif ($pm === '2' || str_contains($pmLower, 'vietqr') || $pmLower === 'bank_transfer') {
                $pmText = 'Chuyển khoản VietQR';
            } elseif ($pm === '3' || $pmLower === 'card') {
                $pmText = 'Quẹt thẻ POS';
            } elseif ($pm !== '') {
                $pmText = $pm;
            }

            $statusText = 'Đang chờ xử lý';
            switch ($order->status) {
                case 0:
                    $statusText = 'Chờ xử lý / Đang phục vụ';
                    break;
                case 1:
                    $statusText = 'Đang xử lý / Đang giao';
                    break;
                case 2:
                    $statusText = 'Hoàn thành';
                    break;
                case 3:
                    $statusText = 'Đã hủy';
                    break;
                case 4:
                    $statusText = 'Đã hoàn tiền';
                    break;
            }

            $itemsCount = $order->orderDetails->where('status', '!=', 'cancelled')->sum('qty');
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
