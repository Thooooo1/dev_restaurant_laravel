<x-layout-backend>
    <div class="space-y-6">
        <!-- Top Action Bar (Tiêu đề bàn, trạng thái và các nút điều hướng dạng pill) -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2">
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">
                        {{ $table->name }} – {{ $table->floor->name ?? 'Khu vực chung' }}
                    </h1>

                    @if ($activeOrder)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-orange-50 text-[#F47716] border border-orange-100">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#F47716]"></span>
                            Đang có khách
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                            Bàn trống
                        </span>
                    @endif
                </div>

                <p class="text-xs text-gray-400 mt-1">
                    {{ $table->seats }} chỗ ngồi
                    @if ($activeOrder)
                        · Đơn hàng #{{ $activeOrder->id }} · {{ $activeOrder->customer_name }}
                    @else
                        · Bàn sẵn sàng đón khách
                    @endif
                </p>
            </div>

            <!-- Action Buttons (Style chuẩn Ant Design giống Ảnh 1) -->
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('admin.table-order.index') }}" 
                   class="ant-btn ant-btn-sm font-medium bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">
                    <span>Quay lại sơ đồ bàn</span>
                </a>

                @if ($activeOrder)
                    <a href="{{ route('admin.table-order.order', $table->id) }}" 
                       class="ant-btn ant-btn-sm font-medium bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">
                        <span>Gọi thêm món</span>
                    </a>
                    <a href="{{ route('admin.table-order.payment', $table->id) }}" 
                       class="ant-btn ant-btn-primary ant-btn-sm font-bold uppercase tracking-wider shadow-2xs">
                        <span>Thanh toán</span>
                    </a>
                @else
                    <a href="{{ route('admin.table-order.order', $table->id) }}" 
                       class="ant-btn ant-btn-primary ant-btn-sm font-bold uppercase tracking-wider shadow-2xs">
                        <span>+ Mở bàn & Gọi món</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- 2-Card Layout (Trái: Chi tiết đơn hàng, Phải: QR Code bàn) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- CỘT TRÁI: CHI TIẾT ĐƠN HÀNG (7-8 CỘT) -->
            <div class="lg:col-span-8">
                <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-2xs">
                    <div class="pb-3 border-b border-gray-100">
                        <h2 class="text-base font-bold text-gray-900">Chi tiết đơn hàng</h2>
                        <p class="text-xs text-gray-400 mt-1">Tên món · Số lượng · Thành tiền · Bếp</p>
                    </div>

                    @if ($activeOrder)
                        @php
                            $orderDetails = $activeOrder->orderDetails;
                            $subtotal = $orderDetails->sum(function($item) {
                                return $item->amount > 0 ? $item->amount : ($item->price * $item->qty);
                            });
                            $discountAmount = ($activeOrder->total_price > 0 && $activeOrder->total_price < $subtotal) 
                                ? ($subtotal - $activeOrder->total_price) 
                                : round($subtotal * 0.1);
                            $finalTotal = $activeOrder->total_price > 0 ? $activeOrder->total_price : max(0, $subtotal - $discountAmount);
                        @endphp

                        <div class="divide-y divide-gray-100 my-2">
                            @forelse ($orderDetails as $item)
                                @php
                                    $st = $item->status;
                                    $stText = 'Chờ bếp';
                                    $stColor = 'text-amber-600';
                                    if ($st == 'cooking' || $st == '1') {
                                        $stText = 'Đang nấu';
                                        $stColor = 'text-blue-600';
                                    } elseif ($st == 'completed' || $st == '2') {
                                        $stText = 'Đã lên bàn';
                                        $stColor = 'text-emerald-600';
                                    }
                                    $itemAmount = $item->amount > 0 ? $item->amount : ($item->price * $item->qty);
                                @endphp
                                <div class="py-3.5 flex items-center justify-between gap-4">
                                    <div class="min-w-0">
                                        <div class="font-semibold text-sm text-gray-900 truncate">
                                            {{ $item->product->name ?? ('Món #' . $item->product_id) }} × {{ $item->qty }}
                                        </div>
                                        <div class="text-xs text-gray-400 mt-0.5 flex items-center gap-1.5 flex-wrap">
                                            <span class="font-mono">{{ number_format($itemAmount, 0, ',', '.') }}đ</span>
                                            <span>·</span>
                                            <span class="{{ $stColor }} font-medium">{{ $stText }}</span>
                                            @if(($item->order_turn ?? 1) > 1)
                                                <span>·</span>
                                                <span class="text-gray-400">Lượt gọi #{{ $item->order_turn }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <span class="font-semibold text-sm text-gray-900 font-mono">
                                            {{ number_format($itemAmount, 0, ',', '.') }}đ
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="py-8 text-center text-gray-400 text-sm">
                                    Chưa có món ăn nào trong đơn hàng này.
                                </div>
                            @endforelse
                        </div>

                        <!-- Footer Tổng thanh toán -->
                        <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                            <span class="font-bold text-base text-gray-900">Tổng thanh toán</span>
                            <span class="text-xl font-bold text-[#C25E00] font-mono">
                                {{ number_format($finalTotal, 0, ',', '.') }}đ
                            </span>
                        </div>
                    @else
                        <!-- Bàn Trống -->
                        <div class="py-12 text-center space-y-3">
                            <div class="w-12 h-12 rounded-full bg-gray-50 text-gray-400 flex items-center justify-center mx-auto text-lg font-bold">
                                {{ $table->seats }}
                            </div>
                            <p class="text-sm font-medium text-gray-700">Bàn hiện đang trống</p>
                            <p class="text-xs text-gray-400 max-w-sm mx-auto">
                                Khách có thể quét mã QR bên cạnh để gọi món hoặc nhân viên mở bàn trực tiếp trên hệ thống POS.
                            </p>
                            <div class="pt-2">
                                <a href="{{ route('admin.table-order.order', $table->id) }}" 
                                   class="ant-btn ant-btn-primary ant-btn-sm font-bold uppercase tracking-wider shadow-2xs">
                                    <span>+ Mở bàn & Gọi món</span>
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- CỘT PHẢI: QR CODE BÀN (4-5 CỘT) -->
            <div class="lg:col-span-4">
                <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-2xs text-center flex flex-col items-center">
                    <h2 class="text-base font-bold text-gray-900 w-full text-left mb-6">
                        QR Code bàn
                    </h2>

                    <!-- QR Box -->
                    <div class="p-3 bg-white rounded-2xl border border-gray-200 inline-block shadow-2xs mb-3" id="qrcode-container">
                        {!! $qrcode !!}
                    </div>

                    <!-- Tên Bàn -->
                    <div class="font-bold text-sm text-gray-900 mt-1">
                        {{ $table->name }}
                    </div>

                    <!-- Badge -->
                    <div class="mt-1.5">
                        <span class="inline-block bg-gray-100 text-gray-600 text-xs px-2.5 py-0.5 rounded-full font-medium">
                            QR gọi món
                        </span>
                    </div>

                    <!-- Mô Tả Nhỏ -->
                    <p class="text-xs text-gray-400 mt-2 text-center max-w-[220px]">
                        Khách quét mã để gọi món tại bàn.
                    </p>

                    <!-- Thao Tác Nút Bấm -->
                    <div class="w-full mt-6 space-y-2">
                        <button type="button" onclick="window.print()" 
                                class="ant-btn w-full justify-center font-medium bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 cursor-pointer">
                            <span>In Standee</span>
                        </button>
                        <button type="button" onclick="downloadQR()" 
                                class="ant-btn w-full justify-center font-medium bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 cursor-pointer">
                            <span>Tải ảnh QR</span>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Printable Standee (Chỉ hiển thị khi bấm In Standee - Không ảnh hưởng đến giao diện web) -->
    <div id="printableStandee" class="hidden p-8 bg-white text-center max-w-sm mx-auto border-2 border-black rounded-2xl">
        <div class="text-xs font-bold uppercase tracking-widest text-[#C25E00] mb-1">CIVILIZE RESTAURANT</div>
        <div class="text-[10px] text-gray-500 uppercase tracking-wider mb-3">Ẩm Thực Phúc Thọ</div>
        <div class="inline-block px-4 py-1.5 bg-black text-white rounded-xl mb-4">
            <span class="text-[10px] text-amber-400 uppercase tracking-widest block">VỊ TRÍ BÀN</span>
            <div class="text-xl font-bold">{{ $table->name }}</div>
            <div class="text-[10px] text-gray-300">{{ $table->floor->name ?? 'Khu vực chung' }}</div>
        </div>
        <div class="p-3 bg-white border border-gray-300 rounded-xl inline-block mb-3">
            {!! $qrcode !!}
        </div>
        <div class="text-xs font-medium text-gray-700 mt-2">Mở Camera hoặc Zalo để quét mã gọi món</div>
        <div class="text-[11px] font-bold text-[#C25E00] mt-3 pt-2 border-t border-gray-200">ƯU ĐÃI: TỰ ĐỘNG GIẢM 10% TẠI BÀN</div>
    </div>

    <!-- Script Chuyển Đổi SVG sang JPG tải về sắc nét -->
    <script>
        function downloadQR() {
            const svg = document.querySelector('#qrcode-container svg');
            if (!svg) {
                alert('Không tìm thấy hình SVG QR');
                return;
            }
            
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const svgData = new XMLSerializer().serializeToString(svg);
            const img = new Image();
            
            const svgBlob = new Blob([svgData], {type: 'image/svg+xml;charset=utf-8'});
            const url = URL.createObjectURL(svgBlob);
            
            img.onload = function() {
                canvas.width = 600;
                canvas.height = 600;
                ctx.fillStyle = 'white';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img, 50, 50, 500, 500);
                
                const jpgUrl = canvas.toDataURL('image/jpeg', 0.95);
                const link = document.createElement('a');
                link.href = jpgUrl;
                link.download = 'QR_{{ Str::slug($table->name) }}.jpg';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
            };
            
            img.src = url;
        }
    </script>

    <!-- CSS In ấn -->
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #printableStandee, #printableStandee * {
                visibility: visible;
            }
            #printableStandee {
                display: block !important;
                position: absolute;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%);
                box-shadow: none !important;
            }
        }
    </style>
</x-layout-backend>
