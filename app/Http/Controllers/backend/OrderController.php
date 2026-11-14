<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $statusFilter = $request->query('status', 5);
        $perPage = 6;

        // Chỉ hiển thị đơn hàng giao tận nơi & mang đi (loại trừ các đơn ăn trực tiếp tại bàn)
        $orders = Order::whereNull('deleted_at')
            ->where(function ($query) {
                $query->whereNull('table_id')
                      ->orWhere('table_id', 0);
            })
            ->when($statusFilter != 5, function ($query) use ($statusFilter) {
                return $query->where('status', $statusFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return view('backend.order.index', compact('orders', 'statusFilter'));
    }

    public function export(Request $request)
    {
        if (!\Illuminate\Support\Facades\Auth::user() || !\Illuminate\Support\Facades\Auth::user()->isAdmin()) {
            return redirect()->route('admin.order.index')->with('error', 'Chỉ Quản trị viên mới có quyền xuất dữ liệu đơn hàng!');
        }
        $status = $request->query('status');
        return \App\Services\ExcelExporter::download(new \App\Exports\OrderExport($status, true), 'danh-sach-don-hang.xls');
    }

    public function edit($id)
    {
        $order = Order::find($id);
        if (!$order) {
            return redirect()->route('admin.order.index')->with('error', 'Đơn hàng không tồn tại');
        }

        return view('backend.order.edit', compact('order'));
    }
    public function update(Request $request, $id)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user || !$user->isAdmin()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Chỉ Quản trị viên (Admin) mới có quyền cập nhật đơn hàng.'], 403);
            }
            abort(403, 'Chỉ Quản trị viên (Admin) mới có quyền cập nhật đơn hàng.');
        }

        // Chỉ cho phép cập nhật status (0..3). Tuyệt đối không cho phép đổi payment_status hay payment_method qua form này
        $request->validate([
            'status' => 'required|integer|in:0,1,2,3',
            'reason' => 'nullable|string|max:1000',
        ], [
            'status.in' => 'Trạng thái đơn hàng không hợp lệ (chỉ chấp nhận từ 0 đến 3).',
        ]);

        try {
            $order = \Illuminate\Support\Facades\DB::transaction(function () use ($request, $id, $user) {
                $order = Order::with('orderDetails.product')->where('id', $id)->lockForUpdate()->firstOrFail();

                $fromStatus = (int)$order->status;
                $toStatus = (int)$request->status;

                if ($toStatus === \App\Enums\OrderStatus::CANCELLED) {
                    // Nếu đơn hàng đã thanh toán, phải thực hiện quy trình hoàn tiền chính thức
                    if ($order->isPaid() || $order->payment_status === Order::PAYMENT_PAID) {
                        $reason = $request->input('reason') ?: 'Admin hủy đơn hàng đã thanh toán';
                        $order->markAsRefunded($reason);
                    }

                    // Hoàn trả tồn kho cho tất cả sản phẩm trong đơn hàng
                    foreach ($order->orderDetails as $detail) {
                        if ($detail->product) {
                            $detail->product->increment('qty', $detail->qty);
                        }
                    }
                } elseif ($toStatus === \App\Enums\OrderStatus::COMPLETED) {
                    if ($order->isPaid() && !$order->revenue_recognized_at) {
                        $order->revenue_recognized_at = now();
                    }
                }

                $order->status = $toStatus;
                $order->updated_by = $user->id;
                $order->save();

                // Release merged tables if order is completed or cancelled
                if (!in_array((int)$order->status, [\App\Enums\OrderStatus::PENDING, \App\Enums\OrderStatus::PROCESSING])) {
                    \App\Models\Table::where('locked_order_id', $order->id)->update(['locked_order_id' => null]);
                }

                // Ghi nhật ký kiểm toán (Audit Trail)
                \App\Models\OrderDeliveryLog::create([
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'from_delivery_status' => $order->delivery_status,
                    'to_delivery_status' => $order->delivery_status,
                    'from_order_status' => $fromStatus,
                    'to_order_status' => $toStatus,
                    'shipper_name' => $order->shipper_name,
                    'shipper_phone' => $order->shipper_phone,
                    'note' => 'Admin cập nhật trạng thái đơn hàng từ ' . \App\Enums\OrderStatus::getLabel($fromStatus) . ' sang ' . \App\Enums\OrderStatus::getLabel($toStatus) . ($request->reason ? (' - Lý do: ' . $request->reason) : ''),
                    'created_at' => now(),
                ]);

                return $order;
            });
        } catch (\DomainException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Lỗi transaction cập nhật đơn hàng: ' . $e->getMessage());
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Lỗi hệ thống khi cập nhật đơn hàng.'], 500);
            }
            return redirect()->back()->with('error', 'Lỗi hệ thống khi cập nhật đơn hàng: ' . $e->getMessage());
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái đơn hàng thành công!',
                'order_status' => (int)$order->status,
                'order_status_label' => $order->status_label,
                'payment_status' => $order->payment_status,
            ]);
        }

        return redirect()->back()->with('success', 'Cập nhật trạng thái đơn hàng thành công!');
    }

    public function updateDelivery(Request $request, $id)
    {
        $request->validate([
            'shipper_name' => 'nullable|string|max:255',
            'shipper_phone' => 'nullable|string|max:50',
            'delivery_status' => 'required|string|in:waiting,picked_up,delivering,delivered,failed',
            'delivery_note' => $request->delivery_status === 'failed' ? 'required|string|max:1000' : 'nullable|string|max:1000',
        ], [
            'delivery_note.required' => 'Vui lòng nhập lý do giao hàng thất bại vào ghi chú.',
        ]);

        try {
            $order = \Illuminate\Support\Facades\DB::transaction(function () use ($request, $id) {
                // 1. Khóa bi quan hàng đơn hàng (Pessimistic Locking) để chống race-condition
                $order = Order::where('id', $id)->lockForUpdate()->firstOrFail();

                if (!$order->isDelivery()) {
                    throw new \DomainException('Thao tác điều phối shipper chỉ áp dụng cho đơn giao hàng (Delivery).');
                }

                $fromDeliveryStatus = $order->delivery_status;
                $fromOrderStatus = (int)$order->status;
                $oldShipperName = $order->shipper_name;
                $oldShipperPhone = $order->shipper_phone;

                // 2. Kiểm tra tính hợp lệ của State Machine (ném DomainException nếu vi phạm)
                $codCollected = $request->has('cod_collected') 
                    ? $request->boolean('cod_collected') 
                    : ($request->input('payment_status') === 'paid' ? true : null);
                $order->applyDeliveryStatusTransition($request->delivery_status, $request->delivery_note, $codCollected);

                // 3. Cập nhật thông tin giao hàng
                $newShipperName = $request->has('shipper_name') ? $request->shipper_name : $oldShipperName;
                $newShipperPhone = $request->has('shipper_phone') ? $request->shipper_phone : $oldShipperPhone;

                $order->shipper_name = $newShipperName;
                $order->shipper_phone = $newShipperPhone;
                $order->delivery_note = $request->delivery_note;
                $order->updated_by = \Illuminate\Support\Facades\Auth::id();
                $order->save();

                // 4. Ghi nhật ký kiểm toán (Audit Trail) trong CÙNG transaction nguyên tử
                \App\Models\OrderDeliveryLog::create([
                    'order_id' => $order->id,
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    'from_delivery_status' => $fromDeliveryStatus,
                    'to_delivery_status' => $order->delivery_status,
                    'from_order_status' => $fromOrderStatus,
                    'to_order_status' => (int)$order->status,
                    'shipper_name' => $order->shipper_name,
                    'shipper_phone' => $order->shipper_phone,
                    'note' => $order->delivery_note,
                    'created_at' => now(),
                ]);

                return $order;
            });
        } catch (\DomainException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Lỗi transaction cập nhật giao hàng: ' . $e->getMessage());
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Lỗi hệ thống khi cập nhật giao hàng.'], 500);
            }
            return redirect()->back()->with('error', 'Lỗi hệ thống khi cập nhật giao hàng: ' . $e->getMessage());
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật tiến trình giao hàng thành công!',
                'delivery_status' => $order->delivery_status,
                'delivery_status_label' => $order->delivery_status_label,
                'order_status' => (int)$order->status,
                'order_status_label' => $order->status_label,
                'payment_status' => $order->payment_status,
            ]);
        }

        return redirect()->back()->with('success', 'Cập nhật tiến trình giao hàng thành công!');
    }

    /**
     * Bàn giao món trực tiếp cho khách (áp dụng cho đơn Takeaway hoặc POS)
     * Thao tác này chuyển trạng thái sang COMPLETED và chính thức ghi nhận doanh thu
     */
    public function markHandedOver(Request $request, $id)
    {
        try {
            $order = \Illuminate\Support\Facades\DB::transaction(function () use ($request, $id) {
                $order = Order::where('id', $id)->lockForUpdate()->firstOrFail();
                $fromStatus = (int)$order->status;
                $actorId = \Illuminate\Support\Facades\Auth::id();
                $note = $request->input('note');

                $order->markAsHandedOver($actorId, $note);

                \App\Models\OrderDeliveryLog::create([
                    'order_id' => $order->id,
                    'user_id' => $actorId,
                    'from_delivery_status' => $order->delivery_status,
                    'to_delivery_status' => $order->delivery_status,
                    'from_order_status' => $fromStatus,
                    'to_order_status' => (int)$order->status,
                    'shipper_name' => $order->shipper_name,
                    'shipper_phone' => $order->shipper_phone,
                    'note' => 'Bàn giao món cho khách: ' . ($note ?: 'Thành công'),
                    'created_at' => now(),
                ]);

                return $order;
            });
        } catch (\DomainException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Lỗi transaction bàn giao món: ' . $e->getMessage());
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Lỗi hệ thống khi bàn giao món.'], 500);
            }
            return redirect()->back()->with('error', 'Lỗi hệ thống khi bàn giao món: ' . $e->getMessage());
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Bàn giao món cho khách thành công!',
                'order_status' => (int)$order->status,
                'order_status_label' => $order->status_label,
                'revenue_recognized_at' => $order->revenue_recognized_at?->toIso8601String(),
            ]);
        }

        return redirect()->back()->with('success', 'Bàn giao món cho khách thành công!');
    }

    /**
     * Xác nhận đã thu đủ tiền mặt COD cho đơn giao hàng (Chỉ dành cho Employee/Admin)
     */
    public function confirmCodCollected($id, Request $request)
    {
        $order = Order::findOrFail($id);

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($order, $request) {
                $order->confirmCodCollected(
                    $request->input('note'),
                    \Illuminate\Support\Facades\Auth::id()
                );
            });
        } catch (\DomainException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Lỗi xác nhận thu COD cho đơn #{$order->id}: " . $e->getMessage());
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Lỗi hệ thống khi xác nhận thu tiền COD.'], 500);
            }
            return redirect()->back()->with('error', 'Lỗi hệ thống khi xác nhận thu COD: ' . $e->getMessage());
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xác nhận thu đủ tiền COD thành công!',
                'payment_status' => $order->payment_status,
                'revenue_recognized_at' => $order->revenue_recognized_at,
            ]);
        }

        return redirect()->back()->with('success', 'Đã xác nhận thu đủ tiền COD thành công!');
    }

    /**
     * Xác nhận thanh toán thủ công cho đơn hàng bất kỳ (VietQR, Chuyển khoản, Tiền mặt)
     */
    public function confirmPaymentManual(Request $request, $id)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user || (!$user->isAdmin() && !$user->isEmployee())) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($id, $user, $request) {
            $order = Order::where('id', $id)->lockForUpdate()->firstOrFail();

            if ($order->payment_status === Order::PAYMENT_PAID) {
                return redirect()->back()->with('info', 'Đơn hàng này đã được xác nhận thanh toán trước đó.');
            }

            $order->payment_status = Order::PAYMENT_PAID;
            $order->paid_at = $order->paid_at ?: now();

            if ($order->isDineIn()) {
                $order->status = \App\Enums\OrderStatus::COMPLETED;
                $order->revenue_recognized_at = now();
                if ($order->table_id) {
                    \App\Models\Table::where('id', $order->table_id)->update(['status' => 0, 'locked_order_id' => null]);
                    \App\Models\Table::where('locked_order_id', $order->id)->update(['locked_order_id' => null]);
                }
            } elseif ($order->isPos()) {
                $order->status = \App\Enums\OrderStatus::COMPLETED;
                $order->revenue_recognized_at = now();
            } else {
                if ((int)$order->status === \App\Enums\OrderStatus::PENDING) {
                    $order->status = \App\Enums\OrderStatus::PROCESSING;
                }
                if ($order->delivery_status === Order::DELIVERY_DELIVERED) {
                    $order->revenue_recognized_at = now();
                }
            }

            $order->updated_by = $user->id;
            $order->save();

            // Ghi nhận nhật ký PaymentTransaction
            try {
                \App\Models\PaymentTransaction::create([
                    'order_id' => $order->id,
                    'reference_code' => 'ADMIN_CONFIRM_' . $order->id . '_' . time(),
                    'amount' => (int) $order->total_price,
                    'content' => 'Nhân viên/Admin xác nhận đã nhận thanh toán',
                    'gateway' => $order->payment_method ?: 'manual',
                    'status' => \App\Models\PaymentTransaction::STATUS_SUCCESS,
                    'raw_payload' => json_encode([
                        'actor_id' => $user->id,
                        'confirmed_at' => (string)now(),
                        'note' => $request->input('note', 'Xác nhận thanh toán thủ công'),
                    ]),
                ]);
            } catch (\Throwable $e) {}

            return redirect()->back()->with('success', "Đã xác nhận thanh toán thành công cho đơn hàng #{$order->id}!");
        });
    }

    public function detail($id)
    {
        try {
            $order = Order::with(['orderDetails.product.category', 'table.floor', 'user', 'deliveryLogs.user', 'paymentTransactions'])->findOrFail($id);
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'cdw1_order_delivery_logs') || str_contains($e->getMessage(), 'cdw1_payment_transactions') || str_contains($e->getMessage(), '1146')) {
                @set_time_limit(180);
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                $order = Order::with(['orderDetails.product.category', 'table.floor', 'user', 'deliveryLogs.user', 'paymentTransactions'])->findOrFail($id);
            } else {
                throw $e;
            }
        }

        return view('backend.order.orderdetail', compact('order'));
    }

    public function print($id)
    {
        $order = Order::with(['orderDetails.product', 'table.floor'])->findOrFail($id);
        $paymentInfo = $order->payment_method_info;
        $paymentMethodText = $paymentInfo['name'];

        $createdAt = $order->created_at ? $order->created_at->format('H:i d/m/Y') : '';
        $customerName = $order->name ?? 'Khách lẻ';
        $customerPhone = $order->phone ?? '---';
        $floorName = $order->table->floor->name ?? '';
        $tableName = $order->table->name ?? '';

        $rows = '';
        foreach ($order->orderDetails as $idx => $detail) {
            $name = $detail->product->name ?? 'N/A';
            $price = (int) ($detail->price ?? 0);
            $qty = (int) ($detail->qty ?? 0);
            $amount = (int) ($detail->amount ?? ($price * $qty));
            $rows .= '<tr>'
                . '<td class="c">' . ($idx + 1) . '</td>'
                . '<td class="l">' . e($name) . '</td>'
                . '<td class="q">' . $qty . '</td>'
                . '<td class="p">' . number_format($price, 0, ',', '.') . ' đ</td>'
                . '<td class="a">' . number_format($amount, 0, ',', '.') . ' đ</td>'
                . '</tr>';
        }

        $total = (int) ($order->total_price ?? $order->orderDetails->sum('amount'));

        $html = '<!doctype html><html lang="vi"><head>'
            . '<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
            . '<title>In hóa đơn</title>'
            . '<style>'
            . '@media print { @page { size: 80mm auto; margin: 3mm; } }'
            . 'html, body { padding:0; margin:0; }'
            . 'body { font-family: Arial, sans-serif; font-size: 12px; width:80mm; }'
            . '.bill { width:74mm; margin:0 auto; }'
            . '.title { text-align:center; font-size:16px; font-weight:700; margin:4px 0 6px; }'
            . '.meta { font-size:11px; margin-bottom:4px; }'
            . '.line { border-top:1px dashed #000; margin:6px 0; }'
            . 'table { width:100%; border-collapse:collapse; font-size:11px; }'
            . 'th, td { padding:4px 0; vertical-align:top; }'
            . 'thead th { border-top:1px dashed #000; border-bottom:1px dashed #000; font-weight:700; }'
            . 'tbody tr td { border-bottom:1px dashed #000; }'
            . '.c { text-align:center; width:24px; }'
            . '.l { text-align:left; }'
            . '.q { text-align:right; white-space:nowrap; width:32px; }'
            . '.p { text-align:right; white-space:nowrap; width:62px; }'
            . '.a { text-align:right; white-space:nowrap; width:64px; }'
            . '.sum { font-weight:700; }'
            . '.footer { text-align:center; font-size:11px; margin-top:8px; }'
            . '</style>'
            . '<script>window.onload=function(){window.focus();window.print();setTimeout(function(){window.close();},200);};</script>'
            . '</head><body>'
            . '<div class="bill">'
            . '<div class="title">HÓA ĐƠN</div>'
            . '<div class="meta">' . ($floorName ? 'Tầng: <strong>' . e($floorName) . '</strong> | ' : '') . 'Bàn: <strong>' . e($tableName) . '</strong></div>'
            . '<div class="meta">Mã HĐ: <strong>#' . e((string) $order->id) . '</strong></div>'
            . '<div class="meta">Khách: <strong>' . e($customerName) . '</strong> | SĐT: <strong>' . e($customerPhone) . '</strong></div>'
            . '<div class="meta">Phương thức thanh toán: <strong>' . e($paymentMethodText) . '</strong></div>'
            . '<div class="meta">Thời gian: <strong>' . e($createdAt) . '</strong></div>'
            . '<div class="line"></div>'
            . '<table><thead><tr><th class="c">#</th><th class="l">Tên món ăn</th><th class="q">SL</th><th class="p">Đơn giá</th><th class="a">Thành tiền</th></tr></thead>'
            . '<tbody>' . $rows . '</tbody>'
            . '<tfoot><tr><td colspan="4" class="l sum">Tổng cộng</td><td class="a sum">' . number_format($total, 0, ',', '.') . ' đ</td></tr></tfoot>'
            . '</table>'
            . '<div class="footer">Cảm ơn quý khách!</div>'
            . '</div></body></html>';

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }
    
}
