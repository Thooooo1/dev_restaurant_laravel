<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Contracts\View\View;

class PaymentReportExport
{
    protected $year;
    protected $quarter;
    protected $month;

    public function __construct($year = null, $quarter = null, $month = null)
    {
        $this->year = $year;
        $this->quarter = $quarter;
        $this->month = $month;
    }

    public function view(): View
    {
        $query = Order::query()
            ->whereNull('deleted_at')
            ->whereIn('status', [1, 2])
            ->with(['table', 'user']);

        if ($this->year) {
            $query->whereYear('created_at', $this->year);
        }

        if ($this->quarter) {
            $startMonth = ($this->quarter - 1) * 3 + 1;
            $endMonth = $startMonth + 2;
            $query->whereMonth('created_at', '>=', $startMonth)
                  ->whereMonth('created_at', '<=', $endMonth);
        }

        if ($this->month) {
            $query->whereMonth('created_at', $this->month);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $cashTotal = 0;
        $vietqrTotal = 0;
        $otherTotal = 0;
        $grandTotal = 0;

        $rows = [];
        foreach ($orders as $order) {
            $pm = $order->payment_method;
            $pmText = 'Tiền mặt';
            if ($pm == '2' || $pm == 'vietqr' || $pm == 'bank_transfer') {
                $pmText = 'Chuyển khoản VietQR';
                $vietqrTotal += (int) $order->total_price;
            } elseif ($pm == '1' || $pm == 'cash') {
                $pmText = 'Tiền mặt';
                $cashTotal += (int) $order->total_price;
            } else {
                $pmText = !empty($pm) ? $pm : 'Khác';
                $otherTotal += (int) $order->total_price;
            }

            $orderStyle = 'Tại bàn';
            if ($order->orderStyle == 2) {
                $orderStyle = 'Giao hàng';
            } elseif ($order->orderStyle == 3) {
                $orderStyle = 'Mang đi';
            }

            $tableName = $order->table ? $order->table->name : 'Giao hàng';

            $rows[] = [
                'id' => $order->id,
                'created_at' => $order->created_at ? $order->created_at->format('d/m/Y H:i') : '',
                'order_style' => $orderStyle,
                'location' => $tableName,
                'customer_name' => $order->customer_name ?: ($order->name ?: 'Khách vãng lai'),
                'customer_phone' => $order->customer_phone ?: ($order->phone ?: ''),
                'payment_method' => $pmText,
                'amount' => (int) $order->total_price,
                'status' => 'Hoàn thành'
            ];

            $grandTotal += (int) $order->total_price;
        }

        return view('backend.order-history.export_payment', [
            'rows' => $rows,
            'cashTotal' => $cashTotal,
            'vietqrTotal' => $vietqrTotal,
            'otherTotal' => $otherTotal,
            'grandTotal' => $grandTotal,
            'totalTransactions' => count($rows),
            'year' => $this->year,
            'quarter' => $this->quarter,
            'month' => $this->month,
        ]);
    }
}
