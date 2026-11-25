<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\Orderdetail;
use Illuminate\Contracts\View\View;

class RevenueExport
{
    protected $year;
    protected $quarter;
    protected $month;

    public function __construct($year, $quarter, $month)
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
            ->with(['user', 'orderDetails.product']);

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

        // Flatten data for the report (bỏ qua món đã hủy, dùng total_price đã đối soát để khớp doanh thu thực nhận)
        $data = [];
        $totalRevenue = (float) $orders->sum('total_price');

        foreach ($orders as $order) {
            $recognizedAt = $order->revenue_recognized_at ?? $order->paid_at ?? $order->created_at;
            $formattedDate = $recognizedAt ? \Carbon\Carbon::parse($recognizedAt)->format('d/m/Y H:i') : '';
            foreach ($order->orderDetails as $detail) {
                if ($detail->status === 'cancelled') {
                    continue;
                }
                $data[] = [
                    'order_id' => $order->id,
                    'created_at' => $formattedDate,
                    'customer_name' => $order->customer_name,
                    'phone' => $order->customer_phone,
                    'product_name' => $detail->product->name ?? 'N/A',
                    'qty' => $detail->qty,
                    'price' => $detail->price,
                    'amount' => $detail->amount,
                ];
            }
        }

        return view('backend.order-history.export', [
            'data' => $data,
            'totalRevenue' => $totalRevenue,
            'year' => $this->year,
            'quarter' => $this->quarter,
            'month' => $this->month
        ]);
    }
}
