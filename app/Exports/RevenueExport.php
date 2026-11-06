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
            ->where('status', 2) // Completed orders
            ->with(['user', 'orderDetails.product']);

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

        // Flatten data for the report
        $data = [];
        $totalRevenue = 0;

        foreach ($orders as $order) {
            foreach ($order->orderDetails as $detail) {
                $data[] = [
                    'order_id' => $order->id,
                    'created_at' => $order->created_at->format('d/m/Y H:i'),
                    'customer_name' => $order->customer_name,
                    'phone' => $order->customer_phone,
                    'product_name' => $detail->product->name ?? 'N/A',
                    'qty' => $detail->qty,
                    'price' => $detail->price,
                    'amount' => $detail->amount,
                ];
                $totalRevenue += $detail->amount;
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
