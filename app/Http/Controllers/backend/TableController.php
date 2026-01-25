<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use App\Models\Table;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TableController extends Controller
{
    public function index(Request $request)
    {
        $query = Table::with('floor');

        // Lọc theo tầng nếu có
        if ($request->has('floor_id') && $request->floor_id != '') {
            $query->where('floor_id', $request->floor_id);
        }

        $tables = $query->orderBy('created_at', 'DESC')
            ->paginate(10)
            ->withQueryString(); // Giữ lại query string khi phân trang

        $floors = Floor::orderBy('id', 'ASC')->get();

        return view('backend.table.index', compact('tables', 'floors'));
    }

    public function create()
    {
        $floors = Floor::orderBy('id', 'ASC')->get();

        return view('backend.table.create', compact('floors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'floor_id'   => 'required|exists:floors,id',
            'name'       => 'required|string|max:100',
            'seats'      => 'required|integer|min:1',
            'sort_order' => 'nullable|integer',
            'status'     => 'required|in:1,2',
        ], [
            'floor_id.required' => 'Vui lòng chọn tầng.',
            'floor_id.exists' => 'Tầng không hợp lệ.',
            'name.required' => 'Tên bàn không được để trống.',
            'name.string' => 'Tên bàn phải là chuỗi ký tự.',
            'name.max' => 'Tên bàn không được vượt quá 100 ký tự.',
            'seats.required' => 'Số lượng chỗ ngồi không được để trống.',
            'seats.integer' => 'Số lượng chỗ ngồi phải là số nguyên.',
            'seats.min' => 'Số lượng chỗ ngồi phải lớn hơn 0.',
            'sort_order.integer' => 'Thứ tự phải là số nguyên.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ]);

        Table::create([
            'floor_id'   => $request->floor_id,
            'name'       => $request->name,
            'seats'      => $request->seats,
            'sort_order' => $request->sort_order ?? 0,
            'status'     => $request->status,
        ]);

        return redirect()->route('admin.table.index')
            ->with('success', 'Thêm bàn ăn thành công');
    }

    public function edit(string $id)
    {
        $table = Table::find($id);
        if (! $table) {
            return redirect()->route('admin.table.index')
                ->with('error', 'Bàn ăn không tồn tại');
        }

        $floors = Floor::orderBy('id', 'ASC')->get();

        return view('backend.table.edit', compact('table', 'floors'));
    }

    public function update(Request $request, string $id)
    {
        $table = Table::find($id);
        if (! $table) {
            return redirect()->route('admin.table.index')
                ->with('error', 'Bàn ăn không tồn tại');
        }

        $request->validate([
            'floor_id'   => 'required|exists:floors,id',
            'name'       => 'required|string|max:100',
            'seats'      => 'required|integer|min:1',
            'sort_order' => 'nullable|integer',
            'status'     => 'required|in:1,2',
        ], [
            'floor_id.required' => 'Vui lòng chọn tầng.',
            'floor_id.exists' => 'Tầng không hợp lệ.',
            'name.required' => 'Tên bàn không được để trống.',
            'name.string' => 'Tên bàn phải là chuỗi ký tự.',
            'name.max' => 'Tên bàn không được vượt quá 100 ký tự.',
            'seats.required' => 'Số lượng chỗ ngồi không được để trống.',
            'seats.integer' => 'Số lượng chỗ ngồi phải là số nguyên.',
            'seats.min' => 'Số lượng chỗ ngồi phải lớn hơn 0.',
            'sort_order.integer' => 'Thứ tự phải là số nguyên.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ]);

        $table->floor_id   = $request->floor_id;
        $table->name       = $request->name;
        $table->seats      = $request->seats;
        $table->sort_order = $request->sort_order ?? 0;
        $table->status     = $request->status;

        $table->save();

        return redirect()->route('admin.table.index')
            ->with('success', 'Cập nhật bàn ăn thành công');
    }

    public function status(string $id)
    {
        $table = Table::find($id);
        if ($table) {
            $table->status = $table->status == 1 ? 2 : 1;
            $table->save();

            return redirect()->route('admin.table.index')
                ->with('success', 'Đã đổi trạng thái bàn ăn');
        }

        return redirect()->route('admin.table.index')
            ->with('error', 'Bàn ăn không tồn tại');
    }

    public function delete(string $id)
    {
        $table = Table::find($id);
        if ($table) {
            $table->delete();

            return redirect()->route('admin.table.index')
                ->with('success', 'Đã chuyển bàn ăn vào thùng rác');
        }

        return redirect()->route('admin.table.index')
            ->with('error', 'Bàn ăn không tồn tại');
    }

    public function trash()
    {
        $tables = Table::onlyTrashed()
            ->with('floor')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('backend.table.trash', compact('tables'));
    }

    public function restore(string $id)
    {
        $table = Table::withTrashed()->where('id', $id)->first();
        if ($table) {
            $table->restore();

            return redirect()->route('admin.table.trash')
                ->with('success', 'Khôi phục bàn ăn thành công');
        }

        return redirect()->route('admin.table.trash')
            ->with('error', 'Bàn ăn không tồn tại');
    }

    public function downloadQr($id)
    {
        $table = Table::find($id);
        if (!$table) {
            return redirect()->back()->with('error', 'Bàn không tồn tại.');
        }

        // Hướng người dùng đến trang login của client
        $url = route('site.login');
        
        // Tạo QR Code
        $qrcode = QrCode::size(300)->generate($url);
        
        return view('backend.table.qr', compact('table', 'qrcode', 'url'));
    }

    public function destroy(string $id)
    {
        $table = Table::withTrashed()->where('id', $id)->first();
        if ($table) {
            $table->forceDelete();

            return redirect()->route('admin.table.trash')
                ->with('success', 'Xóa vĩnh viễn bàn ăn thành công');
        }

        return redirect()->route('admin.table.trash')
            ->with('error', 'Bàn ăn không tồn tại');
    }
}

