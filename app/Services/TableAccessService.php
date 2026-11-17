<?php

namespace App\Services;

use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TableAccessService
{
    /**
     * Kiểm tra user hiện tại có phải là nhân viên hoặc quản trị viên hay không
     */
    public static function isStaff(): bool
    {
        return Auth::check() && (Auth::user()->isEmployee() || Auth::user()->isAdmin());
    }

    /**
     * Xác thực quyền truy cập và thao tác trên bàn ăn
     */
    public static function authorizeTableAccess($table, ?Request $request = null): bool
    {
        // 1. Staff và Admin luôn có quyền thao tác toàn hệ thống
        if (self::isStaff()) {
            return true;
        }

        if (!($table instanceof Table)) {
            $table = Table::find($table);
        }

        if (!$table) {
            return false;
        }

        $sessionKey = 'table_verified_' . $table->id;
        $visitSessionKey = 'table_visit_token_' . $table->id;

        // 2. Kiểm tra token gửi kèm trong request nếu có (quét mã QR)
        $request = $request ?? request();
        $token = $request ? ($request->query('token') ?? $request->input('token') ?? $request->query('qr_token') ?? $request->input('qr_token')) : null;

        if (!empty($token) && $table->isValidQrToken((string)$token)) {
            // Nếu bàn chưa có lượt khách visit_token đang mở, tự động khởi tạo
            if (empty($table->visit_token)) {
                $table->startNewVisit();
            }
            self::setTableVerified($table, $table->visit_token);
            return true;
        }

        // 3. Kiểm tra cờ xác thực trong session của khách
        if (session()->get($sessionKey) === true) {
            // V5 Invariant: Nếu bàn đang có visit_token thì session phải gắn đúng visit_token đó
            // Ngăn chặn khách cũ quay lại bàn ở lượt khách tiếp theo sau khi bàn đã được đóng/mở lượt mới
            if (!empty($table->visit_token)) {
                $savedVisitToken = session()->get($visitSessionKey);
                if (empty($savedVisitToken) || !hash_equals((string)$table->visit_token, (string)$savedVisitToken)) {
                    return false;
                }
            }
            return true;
        }

        return false;
    }

    /**
     * Buộc kiểm tra quyền truy cập bàn ăn, ném 403 nếu không đủ thẩm quyền
     */
    public static function assertTableAccess($table, ?Request $request = null, string $message = 'Bạn không có quyền thao tác trên bàn ăn này. Vui lòng quét mã QR tại bàn.'): void
    {
        if (!self::authorizeTableAccess($table, $request)) {
            abort(403, $message);
        }
    }

    /**
     * Ghi nhận phiên truy cập hợp lệ cho bàn ăn vào Session
     */
    public static function setTableVerified($table, ?string $visitToken = null): void
    {
        $tableId = ($table instanceof Table) ? $table->id : (int)$table;
        session()->put('table_verified_' . $tableId, true);
        if ($visitToken) {
            session()->put('table_visit_token_' . $tableId, (string)$visitToken);
        }
        session()->put('current_table_id', $tableId);
    }

    /**
     * Kết thúc phiên làm việc của khách trên trình duyệt
     * Chỉ xóa cờ session của khách, KHÔNG tự ý thay đổi dữ liệu đơn hàng hay trạng thái bàn trên DB
     */
    public static function clearTableSession($table): void
    {
        $tableId = ($table instanceof Table) ? $table->id : (int)$table;
        session()->forget([
            'table_verified_' . $tableId,
            'table_visit_token_' . $tableId,
            'current_table_id',
            'table_order_id',
        ]);
    }
}
