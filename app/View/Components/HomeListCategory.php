<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Category;
class HomeListCategory extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        // 5 danh mục món ăn cốt lõi cho trang chủ (Lẩu, Nướng, Xào, Chiên, Khai vị)
        $coreCategoryIds = [8, 12, 9, 11, 1];
        $dbCategories = Category::where('status', 1)
            ->whereIn('id', $coreCategoryIds)
            ->get()
            ->keyBy('id');

        $categories = collect();
        foreach ($coreCategoryIds as $cid) {
            if (isset($dbCategories[$cid])) {
                $categories->push($dbCategories[$cid]);
            }
        }

        // Tab gộp Đồ uống (thay vì dàn trải riêng lẻ 5 tab)
        $drinksCategory = (object) [
            'id' => 'drinks',
            'name' => 'Đồ uống',
            'category_ids' => [13, 15, 16, 18, 19],
        ];
        $categories->push($drinksCategory);

        // 8 món chính đặc sắc / Signature bán chạy nhất (Lẩu, Bò, Hải sản, Nướng)
        $featuredProducts = \App\Models\Product::where('status', 1)
            ->whereIn('id', [52, 53, 90, 85, 93, 86, 87, 56])
            ->get();

        if ($featuredProducts->isEmpty()) {
            $featuredProducts = \App\Models\Product::where('status', 1)
                ->whereIn('category_id', [8, 12])
                ->limit(8)
                ->get();
        }

        // Toàn bộ món ăn phục vụ tìm kiếm tức thì ngay tại trang chủ
        $allProducts = \App\Models\Product::with('category')
            ->where('status', 1)
            ->orderBy('id', 'desc')
            ->get();

        return view('components.home-list-category', compact('categories', 'featuredProducts', 'allProducts'));
    }
}
