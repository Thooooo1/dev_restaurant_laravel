<x-layout-backend>
    @php
        $paymentInfo = $order->payment_method_info;
        $totalAmount = (int) ($order->total_price ?: $order->orderDetails->sum('amount'));
        
        $statusConfig = match((int)$order->status) {
            0 => ['label' => 'Đang chờ xử lý', 'bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'dot' => 'bg-amber-500'],
            1 => ['label' => 'Đã xác nhận / Đang chế biến', 'bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'dot' => 'bg-blue-500 animate-pulse'],
            2 => ['label' => 'Đã hoàn tất / Đã thanh toán', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'dot' => 'bg-emerald-500'],
            default => ['label' => 'Đã hủy đơn', 'bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200', 'dot' => 'bg-rose-500'],
        };

        $isTableOrder = !empty($order->table_id);
    @endphp

    <div class="space-y-6">
        <!-- Header & Breadcrumb -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-[#F0F0F0]">
            <div>
                <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-[#F47716]">Trang chủ</a>
                    <span>/</span>
                    <a href="{{ route('admin.order.index') }}" class="hover:text-[#F47716]">Quản lý đơn hàng</a>
                    <span>/</span>
                    <span class="text-gray-800 font-semibold">Đơn #{{ $order->id }}</span>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">
                        Chi Tiết Đơn Hàng #{{ $order->id }}
                    </h1>
                    
                    <!-- Trạng thái đơn -->
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                        {{ $statusConfig['label'] }}
                    </span>

                    <!-- Loại đơn -->
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                        {{ $isTableOrder ? ('Ăn tại bàn: ' . ($order->table ? $order->table->name : 'Bàn #' . $order->table_id)) : 'Đơn giao hàng' }}
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.order.printorder', $order->id) }}" target="_blank" class="ant-btn ant-btn-default text-xs">
                    <span>In hóa đơn (80mm)</span>
                </a>
                @if($isTableOrder && $order->table_id)
                <a href="{{ route('admin.table-order.order', $order->table_id) }}" class="ant-btn ant-btn-pos text-xs">
                    <span>Mở màn hình POS</span>
                </a>
                @endif
                <a href="{{ route('admin.order.index') }}" class="ant-btn ant-btn-default text-xs">
                    <span>Quay lại</span>
                </a>
            </div>
        </div>

        <!-- 4 KPI Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Card 1: Phương thức thanh toán -->
            <div class="ant-card p-4">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Thanh toán</div>
                <div class="text-base font-bold text-gray-900">{{ $paymentInfo['name'] }}</div>
                <div class="text-[11px] text-gray-500 mt-0.5 truncate">{{ $paymentInfo['desc'] }}</div>
                <div class="mt-2 pt-2 border-t border-gray-100 flex items-center justify-between text-xs">
                    <span class="text-gray-400">Trạng thái:</span>
                    <span class="font-bold {{ (int)$order->status === 2 ? 'text-emerald-600' : 'text-amber-600' }}">
                        {{ (int)$order->status === 2 ? 'Đã thanh toán' : 'Chưa thanh toán' }}
                    </span>
                </div>
            </div>

            <!-- Card 2: Tổng giá trị hóa đơn -->
            <div class="ant-card p-4">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Tổng tiền hóa đơn</div>
                <div class="text-xl font-bold font-mono text-[#F47716]">
                    {{ number_format($totalAmount, 0, ',', '.') }} ₫
                </div>
                <div class="text-[11px] text-gray-500 mt-0.5">{{ $order->orderDetails->count() }} món ăn đã gọi</div>
                <div class="mt-2 pt-2 border-t border-gray-100 flex items-center justify-between text-xs">
                    <span class="text-gray-400">Hình thức:</span>
                    <span class="font-medium text-gray-700">{{ $order->order_style_name }}</span>
                </div>
            </div>

            <!-- Card 3: Bàn & Vị trí -->
            <div class="ant-card p-4">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Vị trí / Bàn ăn</div>
                <div class="text-base font-bold text-gray-900 truncate">
                    @if($isTableOrder && $order->table)
                        {{ $order->table->name }} ({{ $order->table->floor->name ?? 'Khu vực chính' }})
                    @else
                        Đơn giao hàng
                    @endif
                </div>
                <div class="text-[11px] text-gray-500 mt-0.5">
                    @if($isTableOrder)
                        Số khách: <strong>{{ $order->number_of_guests ?? 1 }} người</strong>
                    @else
                        Địa chỉ: {{ $order->address }}
                    @endif
                </div>
                <div class="mt-2 pt-2 border-t border-gray-100 flex items-center justify-between text-xs">
                    <span class="text-gray-400">Khách hàng:</span>
                    <span class="font-medium text-gray-800 truncate">{{ $order->name ?: ($order->user->fullname ?? 'Khách lẻ') }}</span>
                </div>
            </div>

            <!-- Card 4: Thời gian tạo đơn -->
            <div class="ant-card p-4">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Thời gian</div>
                <div class="text-base font-bold text-gray-900">
                    {{ $order->created_at ? $order->created_at->format('H:i') : '' }}
                </div>
                <div class="text-[11px] text-gray-500 mt-0.5">
                    Ngày: <strong>{{ $order->created_at ? $order->created_at->format('d/m/Y') : '' }}</strong>
                </div>
                <div class="mt-2 pt-2 border-t border-gray-100 flex items-center justify-between text-xs">
                    <span class="text-gray-400">Cập nhật:</span>
                    <span class="font-medium text-gray-600">{{ $order->updated_at ? $order->updated_at->locale('vi')->diffForHumans() : '' }}</span>
                </div>
            </div>
        </div>

        <!-- 2 Column Layout: Left Details + Right Order Items -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Cột trái: Thông tin khách hàng & Chi tiết thanh toán -->
            <div class="lg:col-span-5 space-y-6">
                <!-- Thẻ Thông tin khách hàng -->
                <div class="ant-card p-5">
                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider mb-4 pb-3 border-b border-gray-100">
                        Thông tin khách hàng
                    </h3>
                    <div class="space-y-3 text-xs">
                        <div class="flex items-center justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500">Họ và tên:</span>
                            <span class="font-bold text-gray-900">{{ $order->name ?: ($order->user->fullname ?? 'Khách lẻ') }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500">Số điện thoại:</span>
                            <a href="tel:{{ $order->phone }}" class="font-mono font-bold text-[#F47716] hover:underline">
                                {{ $order->phone ?: 'Chưa cập nhật' }}
                            </a>
                        </div>
                        @if($order->email)
                        <div class="flex items-center justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500">Email:</span>
                            <span class="text-gray-800">{{ $order->email }}</span>
                        </div>
                        @endif
                        <div class="flex items-start justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500 shrink-0">Địa chỉ / Bàn:</span>
                            <span class="font-medium text-gray-800 text-right max-w-[220px]">
                                {{ $order->address ?: ($order->table ? ($order->table->name . ' - ' . ($order->table->floor->name ?? '')) : 'Tại quán') }}
                            </span>
                        </div>
                        @if($order->note)
                        <div class="pt-2">
                            <span class="text-gray-500 block mb-1">Ghi chú của khách:</span>
                            <div class="p-2.5 bg-amber-50/70 border border-amber-200 rounded text-amber-900 text-xs italic">
                                "{{ $order->note }}"
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Thẻ Chi tiết Phương thức Thanh toán (Chuyển khoản, Tiền mặt, Quẹt thẻ) -->
                <div class="ant-card p-5">
                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider mb-4 pb-3 border-b border-gray-100 flex items-center justify-between">
                        <span>Chi tiết phương thức thanh toán</span>
                        <span class="text-xs font-semibold text-gray-700">
                            {{ $paymentInfo['name'] }}
                        </span>
                    </h3>

                    <!-- Khối thông tin theo phương thức -->
                    @if($paymentInfo['code'] === 'vietqr')
                        <!-- Chuyển khoản VietQR -->
                        <div class="space-y-3">
                            <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg text-xs space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-700 font-semibold">Hình thức:</span>
                                    <span class="font-bold text-gray-900">Chuyển khoản VietQR (Napas 247)</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-700 font-semibold">Ngân hàng:</span>
                                    <span class="font-bold text-gray-800">{{ config('vietqr.bank_name') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-700 font-semibold">Số tài khoản:</span>
                                    <span class="font-mono font-bold text-gray-900 text-sm">{{ config('vietqr.account_no') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-700 font-semibold">Chủ tài khoản:</span>
                                    <span class="font-bold text-gray-800 uppercase">{{ config('vietqr.account_name') }}</span>
                                </div>
                                <div class="flex items-center justify-between pt-1 border-t border-gray-200">
                                    <span class="text-gray-700 font-semibold">Nội dung chuyển khoản:</span>
                                    <span class="font-mono font-bold text-gray-900 bg-white px-2 py-0.5 rounded border border-gray-200">
                                        {{ $order->table_id ? ('BAN' . $order->table_id) : ('DH' . $order->id) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Ảnh QR cho thu ngân -->
                            <div class="p-3 bg-white border border-gray-200 rounded-lg text-center">
                                <span class="text-[11px] font-bold text-gray-700 block mb-2">Mã VietQR thanh toán cho đơn này:</span>
                                <img src="https://img.vietqr.io/image/{{ config('vietqr.bank_id') }}-{{ config('vietqr.account_no') }}-compact2.png?amount={{ $totalAmount }}&addInfo={{ $order->table_id ? ('BAN' . $order->table_id) : ('DH' . $order->id) }}&accountName={{ urlencode(config('vietqr.account_name')) }}" 
                                     alt="Mã VietQR" 
                                     class="w-44 h-44 object-contain mx-auto rounded-lg shadow-2xs border border-gray-100">
                                <span class="text-[10px] text-gray-400 mt-2 block">Khách có thể quét trực tiếp từ ảnh này</span>
                            </div>
                        </div>
                    @elseif($paymentInfo['code'] === 'card')
                        <!-- Quẹt thẻ POS -->
                        <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg text-xs space-y-2">
                            <div class="text-gray-900 font-bold">
                                Thanh toán Quẹt Thẻ Máy POS
                            </div>
                            <p class="text-gray-600 text-[11px] leading-relaxed">
                                Áp dụng cho các loại thẻ ngân hàng: Thẻ ATM nội địa (Napas), Thẻ tín dụng & ghi nợ quốc tế Visa, MasterCard, JCB qua máy POS quẹt thẻ cầm tay tại quầy thu ngân.
                            </p>
                            <div class="pt-2 border-t border-gray-200 flex items-center justify-between text-gray-800 font-semibold">
                                <span>Thiết bị thanh toán:</span>
                                <span class="font-bold text-gray-900">Máy POS Cầm Tay Quầy Thu Ngân</span>
                            </div>
                        </div>
                    @elseif($paymentInfo['code'] === 'cash')
                        <!-- Tiền mặt -->
                        <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg text-xs space-y-2">
                            <div class="text-gray-900 font-bold">
                                Thanh toán Tiền Mặt Trực Tiếp
                            </div>
                            <p class="text-gray-600 text-[11px] leading-relaxed">
                                @if($isTableOrder)
                                    Khách hàng thanh toán tiền mặt trực tiếp cho nhân viên phục vụ tại bàn hoặc tại quầy thu ngân sau khi kết thúc bữa ăn.
                                @else
                                    Khách hàng thanh toán tiền mặt khi nhân viên giao đồ ăn đến địa chỉ (COD).
                                @endif
                            </p>
                        </div>
                    @else
                        <!-- Phương thức thanh toán khác -->
                        <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg text-xs space-y-2">
                            <div class="text-gray-800 font-bold">
                                {{ $paymentInfo['name'] }}
                            </div>
                            <p class="text-gray-600 text-[11px]">
                                {{ $paymentInfo['desc'] }}
                            </p>
                        </div>
                    @endif

                    <!-- Form cập nhật trạng thái & phương thức thanh toán -->
                    <form action="{{ route('admin.order.update', $order->id) }}" method="POST" class="mt-4 pt-4 border-t border-gray-100 space-y-3">
                        @csrf
                        <div class="text-xs font-bold text-gray-700">Cập nhật đơn hàng:</div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] text-gray-500 mb-1">Trạng thái đơn:</label>
                                <select name="status" class="polaris-select w-full text-xs font-medium">
                                    <option value="0" {{ (int)$order->status === 0 ? 'selected' : '' }}>Chờ xử lý</option>
                                    <option value="1" {{ (int)$order->status === 1 ? 'selected' : '' }}>Đang chế biến</option>
                                    <option value="2" {{ (int)$order->status === 2 ? 'selected' : '' }}>Hoàn tất / Đã thanh toán</option>
                                    <option value="3" {{ (int)$order->status === 3 ? 'selected' : '' }}>Đã hủy đơn</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] text-gray-500 mb-1">Phương thức TT:</label>
                                <select name="payment_method" class="polaris-select w-full text-xs font-medium">
                                    <option value="1" {{ ($order->payment_method == 1 || $order->payment_method == 'COD') ? 'selected' : '' }}>Tiền mặt</option>
                                    <option value="2" {{ ($order->payment_method == 2 || $order->payment_method == 'VietQR') ? 'selected' : '' }}>Chuyển khoản VietQR</option>
                                    <option value="3" {{ ($order->payment_method == 3 || $order->payment_method == 'card') ? 'selected' : '' }}>Quẹt thẻ POS</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="ant-btn ant-btn-primary w-full text-xs font-bold py-2 justify-center">
                            Lưu thay đổi
                        </button>
                    </form>
                </div>
            </div>

            <!-- Cột phải: Danh sách món ăn chi tiết & Bảng tổng kết tiền -->
            <div class="lg:col-span-7 space-y-6">
                <div class="ant-card p-5">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                        <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider">
                            Danh sách món ăn ({{ $order->orderDetails->count() }} món)
                        </h3>
                        <span class="text-xs text-gray-500">Lượt gọi món: #{{ $order->order_turn ?? 1 }}</span>
                    </div>

                    <!-- Bảng danh sách món -->
                    <div class="overflow-x-auto border border-gray-100 rounded-lg">
                        <table class="w-full text-xs">
                            <thead class="bg-gray-50 text-gray-600 uppercase font-semibold text-[11px] border-b border-gray-100">
                                <tr>
                                    <th class="py-2.5 px-3 text-center w-10">#</th>
                                    <th class="py-2.5 px-3 text-left">Món ăn</th>
                                    <th class="py-2.5 px-3 text-right">Đơn giá</th>
                                    <th class="py-2.5 px-3 text-center w-16">SL</th>
                                    <th class="py-2.5 px-3 text-right">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($order->orderDetails as $index => $detail)
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="py-3 px-3 text-center text-gray-400 font-mono">{{ $index + 1 }}</td>
                                        <td class="py-3 px-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-11 h-11 rounded-lg bg-gray-100 border border-gray-200 overflow-hidden shrink-0">
                                                    @if($detail->product && $detail->product->image && file_exists(public_path('images/product/' . $detail->product->image)))
                                                        <img src="{{ asset('images/product/' . $detail->product->image) }}" 
                                                             alt="{{ $detail->product->name }}" 
                                                             class="w-full h-full object-cover">
                                                    @else
                                                        <div class="w-full h-full flex items-center justify-center text-gray-400 text-[10px] font-semibold bg-gray-50">
                                                            Món
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <span class="font-bold text-gray-900 block text-xs">
                                                        {{ $detail->product->name ?? 'Món ăn' }}
                                                    </span>
                                                    <span class="text-[10px] text-gray-400">
                                                        {{ $detail->product->category->name ?? 'Món ngon' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-3 text-right font-mono text-gray-700">
                                            {{ number_format($detail->price, 0, ',', '.') }} ₫
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <span class="inline-block px-2 py-0.5 bg-gray-100 font-bold text-gray-800 rounded font-mono text-xs">
                                                {{ $detail->qty }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 text-right font-mono font-bold text-gray-900">
                                            {{ number_format($detail->amount, 0, ',', '.') }} ₫
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-gray-400">
                                            Chưa có chi tiết món ăn trong đơn này
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Bảng tính toán tổng tài chính -->
                    <div class="mt-4 pt-4 border-t border-gray-100 max-w-xs ml-auto space-y-2 text-xs">
                        <div class="flex items-center justify-between text-gray-600">
                            <span>Tiền món ăn:</span>
                            <span class="font-mono font-medium">{{ number_format($order->orderDetails->sum('amount'), 0, ',', '.') }} ₫</span>
                        </div>
                        @if(!$isTableOrder)
                        <div class="flex items-center justify-between text-gray-600">
                            <span>Phí giao hàng:</span>
                            <span class="font-mono text-emerald-600 font-medium">Miễn phí</span>
                        </div>
                        @endif
                        <div class="flex items-center justify-between text-gray-600">
                            <span>Chiết khấu / Giảm giá:</span>
                            <span class="font-mono text-gray-400">0 ₫</span>
                        </div>
                        <div class="pt-2 border-t border-gray-200 flex items-center justify-between text-sm">
                            <span class="font-bold text-gray-900 uppercase">Tổng thanh toán:</span>
                            <span class="font-bold font-mono text-xl text-[#F47716]">
                                {{ number_format($totalAmount, 0, ',', '.') }} ₫
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout-backend>