<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use App\Models\Table;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function index()
    {
        $tables = Table::with('floor')
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

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
            'floor_id'   => 'required|exists:cdw1_floors,id',
            'name'       => 'required|string|max:100',
            'seats'      => 'required|integer|min:1',
            'sort_order' => 'nullable|integer',
            'status'     => 'required|in:1,2',
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
            'floor_id'   => 'required|exists:cdw1_floors,id',
            'name'       => 'required|string|max:100',
            'seats'      => 'required|integer|min:1',
            'sort_order' => 'nullable|integer',
            'status'     => 'required|in:1,2',
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


