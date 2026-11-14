<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Order extends Model
{
    use SoftDeletes;
    protected $table ='order';

    public const DELIVERY_WAITING = 'waiting';
    public const DELIVERY_PICKED_UP = 'picked_up';
    public const DELIVERY_DELIVERING = 'delivering';
    public const DELIVERY_DELIVERED = 'delivered';
    public const DELIVERY_FAILED = 'failed';

    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_FAILED = 'failed';
    public const PAYMENT_REFUNDED = 'refunded';
    
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'number_of_guests',
        'address',
        'shipper_name',
        'shipper_phone',
        'delivery_status',
        'picked_up_at',
        'delivered_at',
        'delivery_note',
        'created_by',
        'updated_by',
        'status',
        'note',
        'orderStyle',
        'table_id',
        'order_turn',
        'payment_method',
        'payment_status',
        'paid_at',
        'revenue_recognized_at',
        'total_price',
    ];

    protected $casts = [
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'paid_at' => 'datetime',
        'revenue_recognized_at' => 'datetime',
    ];

    public function getPaymentStatusLabelAttribute(): string
    {
        return match($this->payment_status) {
            self::PAYMENT_PAID => 'Đã thanh toán',
            self::PAYMENT_FAILED => 'Thanh toán thất bại',
            self::PAYMENT_REFUNDED => 'Đã hoàn tiền',
            default => 'Chưa thanh toán'
        };
    }

    public function getDeliveryStatusLabelAttribute(): string
    {
        return match($this->delivery_status) {
            'picked_up' => 'Shipper đã nhận hàng',
            'delivering' => 'Đang giao hàng',
            'delivered' => 'Đã giao thành công',
            'failed' => 'Giao hàng thất bại',
            default => 'Chờ phân công shipper'
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return \App\Enums\OrderStatus::getLabel((int)$this->status, (bool)$this->table_id);
    }

    public function orderDetails()
    {
        return $this->hasMany(Orderdetail::class, 'order_id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function table()
    {
        return $this->belongsTo(Table::class, 'table_id');
    }

    public function itemLogs()
    {
        return $this->hasMany(OrderItemLog::class, 'order_id')->orderBy('id', 'desc');
    }

    public function deliveryLogs()
    {
        return $this->hasMany(OrderDeliveryLog::class, 'order_id')->orderBy('id', 'desc');
    }

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class, 'order_id')->orderBy('id', 'desc');
    }

    /**
     * Phân loại hình thức nghiệp vụ đơn hàng (Single Source of Truth)
     */
    public function isDineIn(): bool
    {
        return !empty($this->table_id);
    }

    public function isPos(): bool
    {
        if ($this->isDineIn()) {
            return false;
        }

        $addr = trim((string)($this->address ?? ''));
        $name = trim((string)($this->name ?? ''));
        $style = (int)($this->orderStyle ?? 0);

        return $addr === 'Tại quán' 
            || $name === 'Khách POS' 
            || $style === 2;
    }

    public function isTakeaway(): bool
    {
        return (int)($this->orderStyle ?? 0) === 3;
    }

    public function isDelivery(): bool
    {
        return !$this->isDineIn() && !$this->isPos() && !$this->isTakeaway();
    }

    /**
     * Kiểm tra đơn hàng đã thực sự được thanh toán / thu tiền hay chưa
     */
    public function isPaid(): bool
    {
        if ($this->payment_status === self::PAYMENT_REFUNDED || $this->payment_status === self::PAYMENT_FAILED) {
            return false;
        }

        // Đơn giao hàng thất bại (failed): Tiền khách đã trả (nếu có qua VietQR) bị chuyển sang khoản tạm thu chờ xử lý/hoàn tiền, không tính là doanh thu bán hàng thực hiện
        if ($this->delivery_status === self::DELIVERY_FAILED) {
            return false;
        }

        if ($this->payment_status === self::PAYMENT_PAID) {
            return true;
        }

        // Đơn hàng hoàn tất (COMPLETED / 2) và không bị đánh dấu thất bại/hoàn tiền
        if ((int)$this->status === \App\Enums\OrderStatus::COMPLETED) {
            return true;
        }

        // Đơn hàng đang xử lý trực tuyến (VietQR/Momo)
        $pm = (string)$this->payment_method;
        if (in_array($pm, [2, '2', 'VietQR', 'vietqr', 'Momo', 'momo'])) {
            if ($this->payment_status === self::PAYMENT_PAID) {
                return true;
            }
            if ($this->payment_status === self::PAYMENT_PENDING) {
                return false;
            }
            return (int)$this->status >= \App\Enums\OrderStatus::PROCESSING;
        }

        return false;
    }

    /**
     * Scope lọc các đơn hàng đã thực sự thu tiền (Dòng tiền thực thu / Tiền đã về tài khoản hoặc thu tiền mặt)
     */
    public function scopeCollected($query)
    {
        return $query->where('payment_status', self::PAYMENT_PAID)
            ->where(function ($q) {
                $q->whereNull('delivery_status')
                  ->orWhere('delivery_status', '!=', self::DELIVERY_FAILED);
            });
    }

    /**
     * Scope lọc các đơn hàng đã được ghi nhận doanh thu (Doanh thu bán hàng thực hiện - Accrual Accounting)
     * Nguyên tắc:
     * - Đơn tại bàn / tại quầy: Hoàn tất bữa ăn / nhận hàng và đã thanh toán
     * - Đơn giao hàng: Đã giao thành công (delivered) và đã thanh toán
     * - Phải có mốc revenue_recognized_at IS NOT NULL
     * - Loại trừ đơn thất bại và đơn hoàn tiền
     */
    public function scopeRevenueRecognized($query)
    {
        return $query->where('payment_status', self::PAYMENT_PAID)
            ->whereNotNull('revenue_recognized_at')
            ->where(function ($q) {
                $q->whereNull('delivery_status')
                  ->orWhere('delivery_status', '!=', self::DELIVERY_FAILED);
            });
    }

    /**
     * Scope chỉ lọc các đơn hàng đã thực sự ghi nhận doanh thu (hỗ trợ tương thích ngược cho dữ liệu cũ)
     */
    public function scopePaid($query)
    {
        return $query->where(function ($main) {
            // Nhánh 1: Bản ghi chuẩn có revenue_recognized_at
            $main->where(function ($modern) {
                $modern->where('payment_status', self::PAYMENT_PAID)
                       ->whereNotNull('revenue_recognized_at')
                       ->where(function ($d) {
                           $d->whereNull('delivery_status')
                             ->orWhere('delivery_status', '!=', self::DELIVERY_FAILED);
                       });
            })
            // Nhánh 2: Bản ghi tương thích ngược (chưa có revenue_recognized_at)
            ->orWhere(function ($legacy) {
                $legacy->whereNull('revenue_recognized_at')
                       ->where(function ($ch) {
                           // Đơn tại bàn hoặc tại quầy đã COMPLETED
                           $ch->where(function ($dinePos) {
                               $dinePos->where(function ($t) {
                                   $t->whereNotNull('table_id')
                                     ->orWhere('address', 'Tại quán')
                                     ->orWhere('name', 'Khách POS');
                               })->where('status', \App\Enums\OrderStatus::COMPLETED)
                                 ->where(function ($p) {
                                     $p->where('payment_status', self::PAYMENT_PAID)
                                       ->orWhereNull('payment_status');
                                 });
                           })
                           // Đơn giao hàng đã DELIVERED hoặc đơn bất kỳ đã hoàn tất COMPLETED
                           ->orWhere(function ($deliv) {
                               $deliv->where(function ($d) {
                                   $d->where('delivery_status', self::DELIVERY_DELIVERED)
                                     ->orWhere('status', \App\Enums\OrderStatus::COMPLETED);
                               })->where(function ($p) {
                                   $p->where('payment_status', self::PAYMENT_PAID)
                                     ->orWhereNull('payment_status');
                               });
                           });
                       });
            });
        })->where(function ($w) {
            $w->where('payment_status', '!=', self::PAYMENT_REFUNDED)
              ->orWhereNull('payment_status');
        });
    }

    /**
     * Xử lý hoàn tiền cho đơn hàng (đơn VietQR giao thất bại hoặc hủy sau khi đã chuyển khoản)
     */
    public function markAsRefunded(?string $reason = null, ?string $refundTxCode = null): void
    {
        if (!$this->isPaid() && $this->payment_status !== self::PAYMENT_PAID) {
            throw new \DomainException('Đơn hàng chưa ghi nhận thanh toán, không thể thực hiện hoàn tiền.');
        }

        $this->payment_status = self::PAYMENT_REFUNDED;
        $refundNote = 'Hoàn tiền' . ($refundTxCode ? " (Mã GD hoàn: {$refundTxCode})" : '') . ($reason ? ": {$reason}" : '');
        $this->delivery_note = ($this->delivery_note ? $this->delivery_note . ' | ' : '') . $refundNote;
        $this->save();

        // Ghi nhật ký giao dịch hoàn tiền vào PaymentTransaction
        try {
            \App\Models\PaymentTransaction::create([
                'order_id' => $this->id,
                'reference_code' => $refundTxCode ?: ('REFUND_' . $this->id . '_' . time()),
                'amount' => (int) $this->total_price,
                'content' => $refundNote,
                'gateway' => 'vietqr',
                'status' => 'refunded',
                'raw_payload' => json_encode([
                    'action' => 'refund',
                    'order_id' => $this->id,
                    'reason' => $reason,
                    'refund_tx' => $refundTxCode,
                    'timestamp' => now()->toIso8601String(),
                ]),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {}
    }

    /**
     * Xác nhận thu đủ tiền mặt COD từ shipper và chính thức ghi nhận doanh thu
     */
    public function confirmCodCollected(?string $note = null): void
    {
        if ($this->delivery_status !== self::DELIVERY_DELIVERED) {
            throw new \DomainException('Chỉ có thể xác nhận thu tiền COD khi đơn hàng đã giao thành công.');
        }

        $this->payment_status = self::PAYMENT_PAID;
        $this->paid_at = $this->paid_at ?: now();
        $this->revenue_recognized_at = $this->revenue_recognized_at ?: now();
        $this->status = \App\Enums\OrderStatus::COMPLETED;
        if ($note) {
            $this->delivery_note = ($this->delivery_note ? $this->delivery_note . ' | ' : '') . $note;
        }
        $this->save();
    }

    /**
     * Tên và thông tin phương thức thanh toán
     */
    public function getPaymentMethodInfoAttribute(): array
    {
        return \App\Enums\PaymentMethod::getInfo($this->payment_method);
    }

    public function getPaymentMethodNameAttribute(): string
    {
        return $this->payment_method_info['name'];
    }

    /**
     * Tên hình thức phục vụ
     */
    public function getOrderStyleNameAttribute(): string
    {
        if ($this->table_id) {
            return 'Dùng bữa tại bàn';
        }
        if (trim($this->address ?? '') === 'Tại quán' || trim($this->name ?? '') === 'Khách POS') {
            return 'Tại quầy (POS)';
        }
        if ((int)$this->orderStyle === 3) {
            return 'Mang về';
        }
        return 'Giao hàng tận nơi';
    }

    /**
     * Tên khách hàng chuẩn xác của đơn hàng (ưu tiên name trên hóa đơn, sau đó là tài khoản khách hàng, loại trừ tài khoản admin/nhân viên)
     */
    public function getCustomerNameAttribute(): string
    {
        if (!empty(trim($this->name ?? ''))) {
            return trim($this->name);
        }

        if ($this->user && $this->user->roles === 'customer') {
            return $this->user->fullname ?: ($this->user->username ?: 'Khách hàng');
        }

        if ($this->table_id) {
            return 'Khách tại bàn';
        }

        return 'Khách lẻ';
    }

    /**
     * Số điện thoại khách hàng chuẩn xác của đơn hàng
     */
    public function getCustomerPhoneAttribute(): string
    {
        if (!empty(trim($this->phone ?? ''))) {
            return trim($this->phone);
        }

        if ($this->user && $this->user->roles === 'customer' && !empty($this->user->phone)) {
            return $this->user->phone;
        }

        return '---';
    }

    public static array $validDeliveryTransitions = [
        self::DELIVERY_WAITING => [self::DELIVERY_WAITING, self::DELIVERY_PICKED_UP, self::DELIVERY_DELIVERING],
        self::DELIVERY_PICKED_UP => [self::DELIVERY_PICKED_UP, self::DELIVERY_DELIVERING, self::DELIVERY_DELIVERED, self::DELIVERY_FAILED],
        self::DELIVERY_DELIVERING => [self::DELIVERY_DELIVERING, self::DELIVERY_DELIVERED, self::DELIVERY_FAILED],
        self::DELIVERY_FAILED => [self::DELIVERY_FAILED, self::DELIVERY_WAITING],
        self::DELIVERY_DELIVERED => [self::DELIVERY_DELIVERED], // Idempotent no-op
    ];

    /**
     * Chuyen doi tien trinh giao hang va dong bo trang thai don hang tuong ung
     *
     * @throws \DomainException neu trang thai khong hop le hoac vi pham quy tac chuyen doi
     */
    public function applyDeliveryStatusTransition(string $deliveryStatus, ?string $note = null, ?bool $codCollected = null): void
    {
        // 1. Don hang da bi huy thi tuyet doi khong cho phep cap nhat giao hang
        if ((int)$this->status === \App\Enums\OrderStatus::CANCELLED) {
            throw new \DomainException('Đơn hàng đã bị hủy, không thể cập nhật tiến trình giao hàng.');
        }

        // 2. Kiem tra tinh idempotent khi da giao thanh cong
        if ($this->delivery_status === self::DELIVERY_DELIVERED) {
            if ($deliveryStatus === self::DELIVERY_DELIVERED) {
                if ($codCollected && $this->payment_status !== self::PAYMENT_PAID) {
                    $this->payment_status = self::PAYMENT_PAID;
                    $this->paid_at = $this->paid_at ?: now();
                    $this->revenue_recognized_at = $this->revenue_recognized_at ?: now();
                    $this->status = \App\Enums\OrderStatus::COMPLETED;
                    if ($note) {
                        $this->delivery_note = ($this->delivery_note ? $this->delivery_note . ' | ' : '') . $note;
                    }
                }
                return; // Da giao truoc do, khong lam gi ca (khong ghi de delivered_at)
            }
            throw new \DomainException('Đơn hàng đã giao thành công, không thể thay đổi hoặc chuyển ngược trạng thái.');
        }

        // 3. Kiem tra bang chuyen trang thai hop le (State Transition Whitelist)
        $currentStatus = $this->delivery_status ?: self::DELIVERY_WAITING;
        if (!isset(self::$validDeliveryTransitions[$currentStatus]) || !in_array($deliveryStatus, self::$validDeliveryTransitions[$currentStatus])) {
            throw new \DomainException("Không thể chuyển tiến trình giao hàng từ '{$currentStatus}' sang '{$deliveryStatus}'.");
        }

        // 4. Quy trinh xu ly giao hang that bai (failed)
        if ($deliveryStatus === self::DELIVERY_FAILED) {
            if (empty(trim($note ?? '')) && empty(trim($this->delivery_note ?? ''))) {
                throw new \DomainException('Vui lòng nhập lý do giao hàng thất bại vào phần ghi chú.');
            }
            $this->delivery_status = self::DELIVERY_FAILED;
            if ($note) {
                $this->delivery_note = $note;
            }
            $this->status = \App\Enums\OrderStatus::PROCESSING;
            return;
        }

        // 5. Quy trinh dieu phoi lai (tu failed ve waiting)
        if ($currentStatus === self::DELIVERY_FAILED && $deliveryStatus === self::DELIVERY_WAITING) {
            $this->delivery_status = self::DELIVERY_WAITING;
            $this->status = \App\Enums\OrderStatus::PROCESSING;
            if ($note) {
                $this->delivery_note = $note;
            }
            return;
        }

        $this->delivery_status = $deliveryStatus;
        if ($note) {
            $this->delivery_note = $note;
        }

        if ($deliveryStatus === self::DELIVERY_PICKED_UP || $deliveryStatus === self::DELIVERY_DELIVERING) {
            if (!$this->picked_up_at) {
                $this->picked_up_at = now();
            }
            if (in_array((int)$this->status, [\App\Enums\OrderStatus::PENDING, \App\Enums\OrderStatus::PROCESSING])) {
                $this->status = \App\Enums\OrderStatus::PROCESSING; // 1: Dang giao hang / Dang thuc hien
            }
        } elseif ($deliveryStatus === self::DELIVERY_DELIVERED) {
            if (!$this->delivered_at) {
                $this->delivered_at = now();
            }

            // Giao thanh cong chuyen status don sang COMPLETED (hoan tat giao nhan)
            $this->status = \App\Enums\OrderStatus::COMPLETED;

            $isCod = in_array((string)$this->payment_method, [1, '1', 'COD', 'cod', 'cash']) 
                || empty($this->payment_method);

            $hasPaymentNote = $note && (
                str_contains(mb_strtolower($note), 'thanh toan') || 
                str_contains(mb_strtolower($note), 'thanh toán') ||
                str_contains(mb_strtolower($note), 'thu tiền') ||
                str_contains(mb_strtolower($note), 'thu tien')
            );

            $isConfirmedCod = $codCollected === true || ($codCollected === null && $hasPaymentNote);

            if ($isCod) {
                // Với đơn COD: Chỉ đánh dấu đã thanh toán và ghi nhận doanh thu khi ĐÃ XÁC NHẬN thu tiền mặt
                if ($isConfirmedCod) {
                    $this->payment_status = self::PAYMENT_PAID;
                    $this->paid_at = $this->paid_at ?: now();
                    $this->revenue_recognized_at = $this->revenue_recognized_at ?: now();
                } else {
                    // Chưa xác nhận thu tiền COD: Giữ payment_status = pending, không ghi nhận doanh thu
                    if ($this->payment_status !== self::PAYMENT_PAID) {
                        $this->payment_status = self::PAYMENT_PENDING;
                    }
                }
            } else {
                // Đơn đã thanh toán trực tuyến từ trước (VietQR): Giao thành công ghi nhận doanh thu
                if ($this->payment_status === self::PAYMENT_PAID) {
                    $this->revenue_recognized_at = $this->revenue_recognized_at ?: now();
                }
            }
        }
    }
}
