<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ImportGoods;
use App\Models\ImportReceipt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ImportGoodsController extends Controller
{
    /**
     * Danh sách Phiếu Nhập Kho (kèm thống kê và bộ lọc)
     */
    public function index(Request $request)
    {
        $query = ImportReceipt::with(['items', 'creator'])->orderBy('import_date', 'desc');

        // Tìm kiếm theo Mã phiếu hoặc Nhà cung cấp hoặc mặt hàng bên trong
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('supplier_name', 'like', "%{$search}%")
                  ->orWhereHas('items', function($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Lọc theo năm
        if ($request->filled('year')) {
            $query->whereYear('import_date', $request->year);
        }

        // Lọc theo tháng
        if ($request->filled('month')) {
            $query->whereMonth('import_date', $request->month);
        }

        // Thống kê nhanh trong kỳ lọc
        $statsQuery = clone $query;
        $totalReceipts = $statsQuery->count();
        $totalAmount = $statsQuery->sum('total_amount');
        $totalItemTypes = ImportGoods::whereIn('import_receipt_id', $statsQuery->pluck('id'))->count();

        $receipts = $query->paginate(10)->withQueryString();

        return view('backend.import-goods.index', compact(
            'receipts',
            'totalReceipts',
            'totalAmount',
            'totalItemTypes'
        ));
    }

    public function export(Request $request)
    {
        $search = $request->query('search');
        $year = $request->query('year');
        $month = $request->query('month');

        return \App\Services\ExcelExporter::download(
            new \App\Exports\InventoryExport($search, $year, $month),
            'bao-cao-nhap-kho.xls'
        );
    }

    /**
     * Form lập Phiếu Nhập Kho mới
     */
    public function create()
    {
        $suggestedCode = ImportReceipt::generateCode();
        $defaultDate = Carbon::now()->format('Y-m-d\TH:i');
        $currentUser = Auth::user();
        $defaultCreator = $currentUser ? ($currentUser->fullname ?? $currentUser->username) : 'Quản trị viên';

        return view('backend.import-goods.create', compact('suggestedCode', 'defaultDate', 'defaultCreator'));
    }

    /**
     * Lưu Phiếu Nhập Kho và các mặt hàng
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:import_receipts,code',
            'supplier_name' => 'required|string|max:255',
            'supplier_phone' => 'nullable|string|max:50',
            'supplier_address' => 'nullable|string|max:255',
            'import_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.unit' => 'required|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
        ], [
            'code.required' => 'Mã phiếu nhập không được để trống.',
            'code.unique' => 'Mã phiếu nhập này đã tồn tại trong hệ thống.',
            'supplier_name.required' => 'Vui lòng nhập tên nhà cung cấp.',
            'import_date.required' => 'Vui lòng chọn ngày nhập kho.',
            'items.required' => 'Phiếu nhập phải có ít nhất 1 mặt hàng.',
            'items.min' => 'Phiếu nhập phải có ít nhất 1 mặt hàng.',
            'items.*.name.required' => 'Tên hàng hoá không được để trống.',
            'items.*.unit.required' => 'Vui lòng chọn đơn vị tính.',
            'items.*.quantity.required' => 'Số lượng không được để trống.',
            'items.*.price.required' => 'Đơn giá không được để trống.',
        ]);

        DB::beginTransaction();
        try {
            // Tính tổng tiền phiếu
            $totalAmount = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $qty = (float) $item['quantity'];
                $price = (float) $item['price'];
                $amount = $qty * $price;
                $totalAmount += $amount;

                $itemsData[] = [
                    'name' => trim($item['name']),
                    'type' => !empty($item['type']) ? trim($item['type']) : null,
                    'unit' => trim($item['unit']),
                    'quantity' => $qty,
                    'price' => $price,
                    'total_amount' => $amount,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }

            // Tạo phiếu nhập kho
            $receipt = ImportReceipt::create([
                'code' => trim($request->code),
                'supplier_name' => trim($request->supplier_name),
                'supplier_phone' => $request->supplier_phone ? trim($request->supplier_phone) : null,
                'supplier_address' => $request->supplier_address ? trim($request->supplier_address) : null,
                'import_date' => Carbon::parse($request->import_date),
                'total_amount' => $totalAmount,
                'note' => $request->note ? trim($request->note) : null,
                'creator_name' => $request->creator_name ? trim($request->creator_name) : (Auth::user()->fullname ?? Auth::user()->username ?? 'Thủ kho'),
                'created_by' => Auth::id(),
                'status' => 1,
            ]);

            // Gán foreign key, đồng bộ tồn kho sản phẩm và lưu các mặt hàng
            foreach ($itemsData as &$it) {
                $it['import_receipt_id'] = $receipt->id;

                // Đồng bộ tăng tồn kho cho món ăn / sản phẩm tương ứng nếu có
                $matchedProduct = \App\Models\Product::where('name', $it['name'])->first();
                if ($matchedProduct) {
                    $matchedProduct->increment('qty', (int)$it['quantity']);
                }
            }
            ImportGoods::insert($itemsData);

            DB::commit();

            return redirect()->route('admin.import-goods.show', $receipt->id)
                ->with('success', "Đã lập thành công Phiếu nhập kho #{$receipt->code}!");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Có lỗi xảy ra khi lập phiếu: ' . $e->getMessage());
        }
    }

    /**
     * Xem Chi tiết & Mẫu Phiếu Nhập Kho (có nút In phiếu)
     */
    public function show(string $id)
    {
        $receipt = ImportReceipt::with('items')->findOrFail($id);
        $amountInWords = self::convertNumberToWords($receipt->total_amount);

        return view('backend.import-goods.show', compact('receipt', 'amountInWords'));
    }

    /**
     * Màn hình In Phiếu Nhập Kho (In trực tiếp / Khổ A4)
     */
    public function printSlip(string $id)
    {
        $receipt = ImportReceipt::with('items')->findOrFail($id);
        $amountInWords = self::convertNumberToWords($receipt->total_amount);

        return view('backend.import-goods.print', compact('receipt', 'amountInWords'));
    }

    /**
     * Form chỉnh sửa Phiếu Nhập Kho
     */
    public function edit(string $id)
    {
        $receipt = ImportReceipt::with('items')->findOrFail($id);
        return view('backend.import-goods.edit', compact('receipt'));
    }

    /**
     * Cập nhật Phiếu Nhập Kho
     */
    public function update(Request $request, string $id)
    {
        $receipt = ImportReceipt::findOrFail($id);

        $request->validate([
            'code' => 'required|string|max:50|unique:import_receipts,code,' . $id,
            'supplier_name' => 'required|string|max:255',
            'supplier_phone' => 'nullable|string|max:50',
            'supplier_address' => 'nullable|string|max:255',
            'import_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.unit' => 'required|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $qty = (float) $item['quantity'];
                $price = (float) $item['price'];
                $amount = $qty * $price;
                $totalAmount += $amount;

                $itemsData[] = [
                    'import_receipt_id' => $receipt->id,
                    'name' => trim($item['name']),
                    'type' => !empty($item['type']) ? trim($item['type']) : null,
                    'unit' => trim($item['unit']),
                    'quantity' => $qty,
                    'price' => $price,
                    'total_amount' => $amount,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }

            // Cập nhật thông tin phiếu
            $receipt->update([
                'code' => trim($request->code),
                'supplier_name' => trim($request->supplier_name),
                'supplier_phone' => $request->supplier_phone ? trim($request->supplier_phone) : null,
                'supplier_address' => $request->supplier_address ? trim($request->supplier_address) : null,
                'import_date' => Carbon::parse($request->import_date),
                'total_amount' => $totalAmount,
                'note' => $request->note ? trim($request->note) : null,
                'creator_name' => $request->creator_name ? trim($request->creator_name) : $receipt->creator_name,
            ]);

            // Xóa các mặt hàng cũ và thêm danh sách mới
            ImportGoods::where('import_receipt_id', $receipt->id)->delete();
            ImportGoods::insert($itemsData);

            DB::commit();

            return redirect()->route('admin.import-goods.show', $receipt->id)
                ->with('success', "Cập nhật Phiếu nhập kho #{$receipt->code} thành công!");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Có lỗi xảy ra khi cập nhật phiếu: ' . $e->getMessage());
        }
    }

    /**
     * Xóa Phiếu Nhập Kho và toàn bộ mặt hàng liên quan
     */
    public function destroy(string $id)
    {
        $receipt = ImportReceipt::findOrFail($id);
        $code = $receipt->code;

        DB::transaction(function() use ($receipt) {
            ImportGoods::where('import_receipt_id', $receipt->id)->delete();
            $receipt->delete();
        });

        return redirect()->route('admin.import-goods.index')
            ->with('success', "Đã xóa Phiếu nhập kho #{$code}!");
    }

    /**
     * Đọc số tiền ra chữ tiếng Việt (Chuẩn kế toán)
     */
    public static function convertNumberToWords($number): string
    {
        $number = (float) $number;
        if ($number <= 0) return 'Không đồng chẵn.';

        $words = ['không', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
        $units = ['', 'nghìn', 'triệu', 'tỷ', 'nghìn tỷ', 'triệu tỷ'];

        $numStr = (string)(int)$number;
        $length = strlen($numStr);
        $blocks = [];

        while ($length > 0) {
            $take = min(3, $length);
            $start = $length - $take;
            $blocks[] = substr($numStr, $start, $take);
            $length -= $take;
        }

        $result = [];
        $totalBlocks = count($blocks);

        for ($i = $totalBlocks - 1; $i >= 0; $i--) {
            $block = (int)$blocks[$i];
            if ($block == 0) continue;

            $str = str_pad($blocks[$i], 3, '0', STR_PAD_LEFT);
            $h = (int)$str[0];
            $t = (int)$str[1];
            $u = (int)$str[2];

            $blockWords = [];

            // Hàng trăm: chỉ đọc nếu không phải block đầu tiên hoặc h > 0
            if ($i < $totalBlocks - 1 || $h > 0) {
                $blockWords[] = $words[$h] . ' trăm';
            }

            // Hàng chục
            if ($t > 1) {
                $blockWords[] = $words[$t] . ' mươi';
                if ($u == 1) $blockWords[] = 'mốt';
                elseif ($u == 5) $blockWords[] = 'lăm';
                elseif ($u > 0) $blockWords[] = $words[$u];
            } elseif ($t == 1) {
                $blockWords[] = 'mười';
                if ($u == 5) $blockWords[] = 'lăm';
                elseif ($u > 0) $blockWords[] = $words[$u];
            } else {
                if ($u > 0) {
                    if ($i < $totalBlocks - 1 || $h > 0) {
                        $blockWords[] = 'lẻ ' . $words[$u];
                    } else {
                        $blockWords[] = $words[$u];
                    }
                }
            }

            if (!empty($units[$i])) {
                $blockWords[] = $units[$i];
            }

            $result[] = implode(' ', $blockWords);
        }

        $final = trim(implode(' ', $result)) . ' đồng chẵn.';
        return mb_convert_case(mb_substr($final, 0, 1), MB_CASE_UPPER, 'UTF-8') . mb_substr($final, 1);
    }
}
