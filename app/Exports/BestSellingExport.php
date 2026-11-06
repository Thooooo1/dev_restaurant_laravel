<?php

namespace App\Exports;

use App\Models\Orderdetail;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class BestSellingExport
{
    protected $type;

    public function __construct($type = 'all')
    {
        $this->type = $type;
    }

    public function view(): View
    {
        $today = Carbon::today();
        $dateFilterLabel = 'Tất cả thời gian';

        $startDate = null;
        $endDate = null;

        if ($this->type === 'today') {
            $startDate = $today->copy()->startOfDay();
            $endDate = $today->copy()->endOfDay();
            $dateFilterLabel = 'Hôm nay (' . $today->format('d/m/Y') . ')';
        } elseif ($this->type === '7day') {
            $startDate = $today->copy()->subDays(6)->startOfDay();
            $endDate = $today->copy()->endOfDay();
            $dateFilterLabel = '7 ngày gần nhất (' . $startDate->format('d/m') . ' - ' . $endDate->format('d/m/Y') . ')';
        } elseif ($this->type === '30day') {
            $startDate = $today->copy()->subDays(29)->startOfDay();
            $endDate = $today->copy()->endOfDay();
            $dateFilterLabel = '30 ngày gần nhất (' . $startDate->format('d/m') . ' - ' . $endDate->format('d/m/Y') . ')';
        } elseif ($this->type === '12month') {
            $startDate = $today->copy()->subMonths(11)->startOfMonth();
            $endDate = $today->copy()->endOfMonth();
            $dateFilterLabel = '12 tháng gần nhất';
        }

        $query = Orderdetail::query()
            ->select(
                'product_id',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('SUM(CASE WHEN amount > 0 THEN amount ELSE (qty * price) END) as total_revenue')
            )
            ->whereHas('order', function($q) use ($startDate, $endDate) {
                $q->whereNull('deleted_at')
                  ->whereIn('status', [1, 2]);
                if ($startDate && $endDate) {
                    $q->whereBetween('created_at', [$startDate, $endDate]);
                }
            })
            ->groupBy('product_id')
            ->orderBy('total_qty', 'desc');

        $items = $query->get();

        $productIds = $items->pluck('product_id')->toArray();
        $products = Product::with(['category', 'brand'])->whereIn('id', $productIds)->get()->keyBy('id');

        $grandRevenue = (float)$items->sum('total_revenue');
        $grandQty = (int)$items->sum('total_qty');

        $rows = [];
        $rank = 1;
        foreach ($items as $item) {
            $product = $products->get($item->product_id);
            $pName = $product ? $product->name : ('Món #' . $item->product_id);
            $catName = $product && $product->category ? $product->category->name : 'Thực đơn chung';
            $kitchenArea = $product ? ($product->kitchen_area ?? ($product->brand->name ?? 'Bếp chính')) : 'Bếp chính';
            $unit = $product ? ($product->unit ?? 'Món') : 'Món';
            $priceSale = $product ? (float)$product->price_sale : 0;
            $revenue = (float)$item->total_revenue;
            $share = $grandRevenue > 0 ? round(($revenue / $grandRevenue) * 100, 1) : 0;

            $rows[] = [
                'rank' => $rank++,
                'product_id' => $item->product_id,
                'name' => $pName,
                'category' => $catName,
                'kitchen_area' => $kitchenArea,
                'unit' => $unit,
                'price_sale' => $priceSale,
                'total_qty' => (int)$item->total_qty,
                'total_revenue' => $revenue,
                'share' => $share
            ];
        }

        return view('backend.dashboard.export_best_selling', [
            'rows' => $rows,
            'grandRevenue' => $grandRevenue,
            'grandQty' => $grandQty,
            'totalDishes' => count($rows),
            'dateFilterLabel' => $dateFilterLabel
        ]);
    }
}
