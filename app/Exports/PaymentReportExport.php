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
            ->paid()
            ->with(['table', 'user']);

        $dateExpr = 'COALESCE(revenue_recognized_at, paid_at, created_at)';

        if ($this->year) {
            $query->whereRaw("YEAR({$dateExpr}) = ?", [$this->year]);
        }

        if ($this->quarter) {
            $startMonth = ($this->quarter - 1) * 3 + 1;
            $endMonth = $startMonth + 2;
            $query->whereRaw("MONTH({$dateExpr}) >= ? AND MONTH({$dateExpr}) <= ?", [$startMonth, $endMonth]);
        }

        if ($this->month) {
            $query->whereRaw("MONTH({$dateExpr}) = ?", [$this->month]);
        }

        $orders = $query->orderByRaw("{$dateExpr} desc")->get();

        $cashTotal = 0;
        $vietqrTotal = 0;
        $otherTotal = 0;
        $grandTotal = 0;

        $rows = [];
        foreach ($orders as $order) {
            $pm = (string) ($order->payment_method ?? '');
            $pmLower = strtolower(trim($pm));
            if ($pm === '2' || str_contains($pmLower, 'vietqr') || $pmLower === 'bank_transfer') {
                $pmText = 'Chuyển khoản VietQR';
                $vietqrTotal += (int) $order->total_price;
            } elseif ($pm === '1' || $pmLower === 'cash' || $pmLower === 'cod') {
                $pmText = $pmLower === 'cod' ? 'Tiền mặt (COD)' : 'Tiền mặt';
                $cashTotal += (int) $order->total_price;
            } elseif ($pm === '3' || $pmLower === 'card') {
                $pmText = 'Quẹt thẻ POS';
                $otherTotal += (int) $order->total_price;
            } else {
                $pmText = $pm !== '' ? $pm : 'Khác';
                $otherTotal += (int) $order->total_price;
            }

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

            $tableName = $order->table ? $order->table->name : 'Giao hàng';
            $recognizedAt = $order->revenue_recognized_at ?? $order->paid_at ?? $order->created_at;

            $rows[] = [
                'id' => $order->id,
                'created_at' => $recognizedAt ? \Carbon\Carbon::parse($recognizedAt)->format('d/m/Y H:i') : '',
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
