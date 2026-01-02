<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::query()->with('category', 'brand');

        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category );
        }

        if ($request->has('brand') && $request->brand) {
            $query->where('brand_id', $request->brand);
        }
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }


        $products = $query->orderBy('created_at', 'DESC')->paginate(8)->appends(request()->query());


        $categories = \App\Models\Category::all();
        $brands = \App\Models\Brand::all();

        return view('backend.product.index', compact('products', 'categories', 'brands'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $query = Product::query()->with('category');
        $product = Product::get();
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }
        if ($request->has('brand') && $request->brand) {
            $query->where('brand_id', $request->brand);
        }

        $categories = \App\Models\Category::select('id', 'name')->get();
        $brands = \App\Models\Brand::select('id', 'name')->get();
        return view('backend.product.create', compact('categories', 'brands', 'product'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $product = new Product();
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = $file->extension();
            $filename = date('YmdHis') . "." . $extension;
            $file->move(public_path('images/product'), $filename);
            $product->image = $filename;

            $product->name = $request->name;
            $product->slug = $request->slug;
            $product->content = $request->content;
            $product->description = $request->description;
            $product->price_buy = $request->price_buy;
            $product->price_sale = $request->price_sale;
            $product->qty = $request->qty;
            $product->category_id = $request->category_id;
            $product->brand_id = $request->brand_id;
            $product->created_by = Auth::id() ?? 1;
            $product->created_at = date('Y-m-d H:i:s');
            $product->status = $request->status;
            $product->new_p = $request->new_p;
            $product->save();
            return redirect()->route('admin.product.index')->with('success', 'Thêm thành công');
        } else {
            return back()->with('error', 'Chưa chọn hình');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $query = Product::query()->with('category', 'brand');
        $product = Product::where('id', $id)->first();
        if ($product == null) {
            return redirect()->back()->with('error', 'Không tồn tại ');
        }
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }
        if ($request->has('brand') && $request->brand) {
            $query->where('brand_id', $request->brand);
        }

        $categories = \App\Models\Category::select('id', 'name')->get();
        $brands = \App\Models\Brand::select('id', 'name')->get();
        return view('backend.product.show', compact('product', 'categories', 'brands'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return redirect()->route('admin.product.index')->with('error', 'Món ăn không tồn tại.');
        }
        $categories = \App\Models\Category::select('id', 'name')->get();
        $brands = \App\Models\Brand::select('id', 'name')->get();

        return view('backend.product.edit', compact('product', 'categories', 'brands'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, string $id)
    {
        $product = Product::where('id', $id)->first();
        $product->name = $request->name;
        $product->slug = $request->slug;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;
        // upload file 
        if ($request->hasFile('image')) {
            //xoa hinh
            if ($product->image && File::exists(public_path("images/product/" . $product->image))) {
                File::delete(public_path("images/product/" . $product->image));
            }
            $file = $request->file('image');
            $extension = $file->extension();
            $filename = date('YmdHis') . "." . $extension;
            $file->move(public_path("images/product"), $filename);
            $product->image = $filename;
        }
        //end upload file
        $product->content = $request->content;
        $product->description = $request->description;
        $product->price_buy = $request->price_buy;
        $product->price_sale = $request->price_sale;
        $product->qty = $request->qty;
        $product->updated_by = Auth::id() ?? 1;
        $product->updated_at = date('Y-m-d H:i:s');
        $product->status = $request->status;
        $product->new_p = $request->new_p;
        if ($product->save()) {
            return redirect()->route('admin.product.index')->with('success', 'Món ăn đã được cập nhật');
        }
        return redirect()->back()->with('error', 'Lỗi thêm');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::withTrashed()->where('id', $id)->first();
        if ($product != null) {
            // xóa hình
            if ($product->image && File::exists(public_path("images/product/" . $product->image))) {
                File::delete(public_path("images/product/" . $product->image));
            }
            $product->forceDelete();
            return redirect()->route("admin.product.trash")->with('success', 'Xóa thành công');
        }
        return redirect()->route("admin.product.trash")->with('error', 'Mẫu tin không tồn tại');
    }
    //danhsachsanphamrac
    public function trash()
    {
        $products = Product::onlyTrashed()->orderBy('created_at', 'DESC')->get();
        return view('backend.product.trash', compact('products'));
    }

    public function status(string $id)
    {
        $product = Product::find($id);
        if ($product != null) {
            $product->status = ($product->status == 1) ? 2 : 1;
            $product->save();

            return redirect()->route('admin.product.index')->with('success', 'Trạng thái đã được thay đổi');
        }
        return redirect()->route('admin.product.index')->with('error', 'Mẫu tin không tồn tại');
    }
    public function delete(string $id)
    {
        $product = Product::find($id);
        if ($product) {
            $product->delete();
            return redirect()->route("admin.product.index")->with('success', 'Xóa vào thùng rác thành công');
        }
        return redirect()->route("admin.product.index")->with('error', 'Mẫu tin không tồn tại');
    }

    public function restore(string $id)
    {
        $product = Product::withTrashed()->where('id', $id);
        if ($product->first() != null) {
            $product->restore();
            return redirect()->route("admin.product.trash")->with('success', 'Khôi phục thành công');
        }
        return redirect()->route("admin.product.trash")->with('error', 'Mẫu này không tồn tại');
    }
    public function showReviews(Request $request, $id)
    {
        $product = Product::with('reviews.user')->find($id);
        if (!$product) {
            return redirect()->route('admin.product.index')->with('error', 'Sản phẩm không tồn tại');
        }

        $rating = $request->query('rating');
        $unresponded = $request->query('unresponded');

        $reviewsQuery = \App\Models\Review::where('product_id', $id)->with('user');

        if ($rating) {
            $reviewsQuery->where('rating', $rating);
        }

        if ($unresponded) {
            $reviewsQuery->whereNull('response');
        }

        $reviews = $reviewsQuery->paginate(5);

        return view('backend.product.review', compact('product', 'reviews', 'rating', 'unresponded'));
    }

}
