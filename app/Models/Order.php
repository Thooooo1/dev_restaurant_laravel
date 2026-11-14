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
        'total_price',
    ];

    protected $casts = [
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'paid_at' => 'datetime',
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
     * Kiểm tra đơn hàng đã thực sự được thanh toán / thu tiền hay chưa
     */
    public function isPaid(): bool
    {
        if ($this->payment_status === self::PAYMENT_REFUNDED || $this->payment_status === self::PAYMENT_FAILED) {
            return false; // Đã hoàn tiền hoặc thất bại thì không tính vào doanh thu thực thu
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
            // Fallback neu payment_status null (ban ghi cu hoac new Order() chua khoi tao)
            return (int)$this->status >= \App\Enums\OrderStatus::PROCESSING;
        }

        return false;
    }

    /**
     * Scope chỉ lọc các đơn hàng đã thực sự thu tiền / ghi nhận doanh thu (loại trừ đã hoàn tiền và giao thất bại)
     */
    public function scopePaid($query)
    {
        return $query->where(function ($q) {
            // 1. Đơn tại bàn (Dine-in), tại quầy (POS), hoặc đơn giao hàng đã hoàn tất / đã giao thành công
            $q->where(function ($completed) {
                $completed->where(function ($ch) {
                    $ch->whereNotNull('table_id')
                       ->orWhere('address', 'Tại quán')
                       ->orWhere('name', 'Khách POS')
                       ->orWhere('status', \App\Enums\OrderStatus::COMPLETED)
                       ->orWhere('delivery_status', self::DELIVERY_DELIVERED);
                })->where('payment_status', self::PAYMENT_PAID);
            })
            // 2. Hoặc đơn hoàn tất COMPLETED (với fallback tương thích ngược cho dữ liệu cũ chưa có payment_status)
            ->orWhere(function ($sub) {
                $sub->where('status', \App\Enums\OrderStatus::COMPLETED)
                    ->where(function ($deliv) {
                        $deliv->whereNull('delivery_status')
                              ->orWhere('delivery_status', '!=', self::DELIVERY_FAILED);
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
    public function markAsRefunded(?string $reason = null): void
    {
        if (!$this->isPaid() && $this->payment_status !== self::PAYMENT_PAID) {
            throw new \DomainException('Đơn hàng chưa ghi nhận thanh toán, không thể thực hiện hoàn tiền.');
        }

        $this->payment_status = self::PAYMENT_REFUNDED;
        if ($reason) {
            $this->delivery_note = ($this->delivery_note ? $this->delivery_note . ' | ' : '') . 'Hoàn tiền: ' . $reason;
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
    public function applyDeliveryStatusTransition(string $deliveryStatus, ?string $note = null): void
    {
        // 1. Don hang da bi huy thi tuyet doi khong cho phep cap nhat giao hang
        if ((int)$this->status === \App\Enums\OrderStatus::CANCELLED) {
            throw new \DomainException('Đơn hàng đã bị hủy, không thể cập nhật tiến trình giao hàng.');
        }

        // 2. Kiem tra tinh idempotent khi da giao thanh cong
        if ($this->delivery_status === self::DELIVERY_DELIVERED) {
            if ($deliveryStatus === self::DELIVERY_DELIVERED) {
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
            if (!$this->paid_at) {
                $this->paid_at = now();
            }
            $this->status = \App\Enums\OrderStatus::COMPLETED; // 2: Da giao hang thanh cong / Hoan tat
            // Với đơn COD / tiền mặt: Giao thành công đồng nghĩa tài xế đã thu tiền mặt từ khách
            if ($this->payment_status !== self::PAYMENT_PAID) {
                $this->payment_status = self::PAYMENT_PAID;
            }
        }
    }
}
