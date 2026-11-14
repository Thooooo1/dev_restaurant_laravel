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
        'total_price',
    ];

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

    /**
     * Kiểm tra đơn hàng đã thực sự được thanh toán / thu tiền hay chưa
     */
    public function isPaid(): bool
    {
        $pm = (string)$this->payment_method;
        $isTableOrder = !empty($this->table_id);

        if ($isTableOrder) {
            return (int)$this->status === \App\Enums\OrderStatus::COMPLETED
                || ((int)$this->status === \App\Enums\OrderStatus::PROCESSING && in_array($pm, ['2', 'VietQR']));
        }

        // Đơn giao hàng / mang về thanh toán trực tuyến (VietQR/Momo) đã nhận webhook xác nhận
        if (in_array($pm, ['VietQR', 'Momo', '2'])) {
            return (int)$this->status >= \App\Enums\OrderStatus::PROCESSING;
        }

        // Đơn giao hàng COD: Chỉ ghi nhận đã thu tiền khi giao thành công (delivered & COMPLETED)
        if (in_array(strtolower($pm), ['1', 'cod', 'cash', 'tiền mặt', ''])) {
            return $this->delivery_status === self::DELIVERY_DELIVERED 
                && (int)$this->status === \App\Enums\OrderStatus::COMPLETED;
        }

        return (int)$this->status === \App\Enums\OrderStatus::COMPLETED;
    }

    /**
     * Scope chỉ lọc các đơn hàng đã thực sự thu tiền / ghi nhận doanh thu
     */
    public function scopePaid($query)
    {
        return $query->where(function ($q) {
            $q->where('status', \App\Enums\OrderStatus::COMPLETED)
              ->orWhere(function ($sub) {
                  $sub->where('status', \App\Enums\OrderStatus::PROCESSING)
                      ->whereIn('payment_method', [2, '2', 'VietQR', 'vietqr', 'Momo', 'momo']);
              });
        });
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

        // 2. Chan quay nguoc trang thai tu da giao ve cac buoc truoc
        if ($this->delivery_status === self::DELIVERY_DELIVERED && $deliveryStatus !== self::DELIVERY_DELIVERED) {
            throw new \DomainException('Đơn hàng đã giao thành công, không thể chuyển ngược trạng thái.');
        }

        // 3. Quy trinh xu ly giao hang that bai (failed)
        if ($deliveryStatus === self::DELIVERY_FAILED) {
            if (empty(trim($note ?? '')) && empty(trim($this->delivery_note ?? ''))) {
                throw new \DomainException('Vui lòng nhập lý do giao hàng thất bại vào phần ghi chú.');
            }
            $this->delivery_status = self::DELIVERY_FAILED;
            if ($note) {
                $this->delivery_note = $note;
            }
            // Giữ nguyên trạng thái PROCESSING để nhân viên xử lý tiếp (điều phối lại hoặc hủy)
            $this->status = \App\Enums\OrderStatus::PROCESSING;
            return;
        }

        // 4. Quy trinh dieu phoi lai (tu failed ve waiting)
        if ($this->delivery_status === self::DELIVERY_FAILED && $deliveryStatus === self::DELIVERY_WAITING) {
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
            $this->status = \App\Enums\OrderStatus::COMPLETED; // 2: Da giao hang thanh cong / Hoan tat
        }
    }
}
