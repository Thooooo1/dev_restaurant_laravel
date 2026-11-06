<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Contracts\View\View;

class ProductExport
{
    protected $categoryId;
    protected $brandId;
    protected $status;
    protected $search;

    public function __construct($categoryId = null, $brandId = null, $status = null, $search = null)
    {
        $this->categoryId = $categoryId;
        $this->brandId = $brandId;
        $this->status = $status;
        $this->search = $search;
    }

    public function view(): View
    {
        $query = Product::query()->with(['category', 'brand']);

        if ($this->categoryId) {
            $query->where('category_id', $this->categoryId);
        }
        if ($this->brandId) {
            $query->where('brand_id', $this->brandId);
        }
        if ($this->status !== null && $this->status !== '') {
            $query->where('status', (int)$this->status);
        }
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        $products = $query->orderBy('name', 'asc')->get();

        $rows = [];
        foreach ($products as $p) {
            $cost = (float)($p->price_buy ?? 0);
            $price = (float)($p->price_sale ?? 0);
            $profit = max(0, $price - $cost);
            $margin = $price > 0 ? round(($profit / $price) * 100, 1) : 0;

            $rows[] = [
                'id' => $p->id,
                'name' => $p->name,
                'category_name' => $p->category->name ?? 'Chưa phân loại',
                'kitchen_area' => $p->kitchen_area ?? ($p->brand->name ?? 'Quầy pha chế'),
                'unit' => $p->unit ?? 'Ly',
                'price_buy' => $cost,
                'price_sale' => $price,
                'profit' => $profit,
                'margin' => $margin,
                'qty' => $p->qty ?? 0,
                'status' => $p->status == 1 ? 'Đang bán' : 'Tạm dừng'
            ];
        }

        return view('backend.product.export', [
            'rows' => $rows,
            'totalItems' => count($rows)
        ]);
    }
}
