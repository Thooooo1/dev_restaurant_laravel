<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ImportGoods;

class ImportGoodsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ImportGoods::query();

        // Tìm kiếm theo tên
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Lọc theo năm
        if ($request->has('year') && $request->year != '') {
            $query->whereYear('created_at', $request->year);
        }

        // Lọc theo tháng
        if ($request->has('month') && $request->month != '') {
            $query->whereMonth('created_at', $request->month);
        }

        $items = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('backend.import-goods.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.import-goods.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
        ], [
            'name.required' => 'Tên hàng hoá là bắt buộc',
            'quantity.required' => 'Số lượng là bắt buộc',
            'price.required' => 'Đơn giá là bắt buộc',
            'unit.required' => 'Đơn vị tính là bắt buộc',
        ]);

        $data = $request->all();
        $data['total_amount'] = $data['quantity'] * $data['price'];

        ImportGoods::create($data);

        return redirect()->route('admin.import-goods.index')->with('success', 'Thêm mới hàng hoá thành công');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $item = ImportGoods::findOrFail($id);
        return view('backend.import-goods.edit', compact('item'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
        ], [
            'name.required' => 'Tên hàng hoá là bắt buộc',
            'quantity.required' => 'Số lượng là bắt buộc',
            'price.required' => 'Đơn giá là bắt buộc',
            'unit.required' => 'Đơn vị tính là bắt buộc',
        ]);

        $item = ImportGoods::findOrFail($id);
        $data = $request->all();
        $data['total_amount'] = $data['quantity'] * $data['price'];

        $item->update($data);

        return redirect()->route('admin.import-goods.index')->with('success', 'Cập nhật hàng hoá thành công');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = ImportGoods::findOrFail($id);
        $item->delete();
        return redirect()->route('admin.import-goods.index')->with('success', 'Xóa hàng hoá thành công');
    }
}
