<x-layout-backend>

    <!-- Top Action Bar (Clean Typography, No Cartoon Icons) -->
    <div class="mb-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 pb-4 border-b border-[#F0F0F0]">
        <div>
            <div class="flex items-center gap-2 text-xs text-[rgba(0,0,0,0.45)] mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-[#F47716]">Trang chủ</a>
                <span>/</span>
                <a href="{{ route('admin.table-order.index') }}" class="hover:text-[#F47716]">Bán hàng & Quản lý bàn (POS)</a>
                <span>/</span>
                <span class="text-[rgba(0,0,0,0.85)] font-semibold">{{ $table->name }}</span>
            </div>
            
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-xl sm:text-2xl font-bold text-[rgba(0,0,0,0.85)] tracking-tight">
                    {{ $table->name }}
                    <span class="text-sm font-normal text-gray-500">({{ $table->floor->name ?? 'Khu vực chung' }})</span>
                </h1>

                @if ($activeOrder)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                        ĐANG CÓ KHÁCH (Đơn #{{ $activeOrder->id }})
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                        <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                        BÀN TRỐNG
                    </span>
                @endif
            </div>

            <p class="text-xs text-[rgba(0,0,0,0.45)] mt-1">
                {{ $table->seats }} chỗ ngồi • Khách dùng điện thoại quét mã QR để xem thực đơn, gọi món vào bếp và quét mã VietQR thanh toán trực tiếp.
            </p>
        </div>

        <!-- Quick Action Buttons -->
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.table-order.index') }}" class="ant-btn ant-btn-sm font-medium">
                Quay lại sơ đồ bàn (POS)
            </a>

            @if ($activeOrder)
                <a href="{{ route('admin.table-order.payment', $table->id) }}" class="ant-btn ant-btn-sm bg-emerald-600 text-white hover:bg-emerald-700 border-none font-bold shadow-xs">
                    Thanh toán bàn này
                </a>
                <a href="{{ route('admin.table-order.order', $table->id) }}" class="ant-btn ant-btn-sm bg-orange-500 text-white hover:bg-orange-600 border-none font-semibold">
                    Gọi thêm món
                </a>
            @else
                <a href="{{ route('admin.table-order.order', $table->id) }}" class="ant-btn ant-btn-sm bg-orange-500 text-white hover:bg-orange-600 border-none font-semibold">
                    Mở bàn & Gọi món (POS)
                </a>
            @endif

            <button type="button" onclick="window.print()" class="ant-btn ant-btn-sm font-medium">
                In Standee Bàn
            </button>
            <button type="button" onclick="downloadQR()" class="ant-btn ant-btn-sm ant-btn-primary font-medium">
                Tải ảnh JPG
            </button>
        </div>
    </div>

    <!-- Layout 2 Cột: Bên trái là Quản lý đơn hàng & món ăn bàn đang dùng, Bên phải là Thẻ Standee QR -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- CỘT 1: THÔNG TIN PHỤC VỤ & MÓN ĂN BÀN ĐANG GỌI (7 CỘT) -->
        <div class="lg:col-span-7 space-y-5">
            @if ($activeOrder)
                @php
                    $orderDetails = $activeOrder->orderDetails;
                    $dishesCount = $orderDetails->count();
                    $subtotal = $orderDetails->sum('amount');
                    $discountAmount = ($activeOrder->total_price > 0 && $activeOrder->total_price < $subtotal) 
                        ? ($subtotal - $activeOrder->total_price) 
                        : round($subtotal * 0.1);
                    $finalTotal = $activeOrder->total_price > 0 ? $activeOrder->total_price : max(0, $subtotal - $discountAmount);
                @endphp

                <!-- Card Chi Tiết Đơn Hàng Đang Phục Vụ -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <!-- Header Đơn Hàng -->
                    <div class="p-4 sm:p-5 bg-gradient-to-r from-stone-900 to-stone-800 text-white flex items-center justify-between flex-wrap gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs uppercase font-bold tracking-wider text-amber-400">Đơn Hàng Đang Phục Vụ</span>
                                <span class="px-2 py-0.5 rounded bg-white/10 text-white font-mono text-xs font-bold">#{{ $activeOrder->id }}</span>
                            </div>
                            <h2 class="text-lg font-bold text-white mt-0.5">
                                {{ $activeOrder->customer_name }}
                                @if($activeOrder->customer_phone && $activeOrder->customer_phone !== '---')
                                    <span class="text-xs text-stone-300 font-normal">({{ $activeOrder->customer_phone }})</span>
                                @endif
                            </h2>
                        </div>

                        <div class="text-right">
                            <span class="text-[11px] text-stone-400 block">Thời gian vào bàn</span>
                            <span class="text-xs font-bold text-amber-300">
                                {{ $activeOrder->created_at ? $activeOrder->created_at->format('H:i') : '' }}
                                ({{ $activeOrder->created_at ? $activeOrder->created_at->locale('vi')->diffForHumans() : 'Vừa xong' }})
                            </span>
                        </div>
                    </div>

                    <!-- Bảng Danh Sách Món Ăn Bàn Đang Dùng -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                    <th class="py-3 px-4">Món ăn / Đồ uống</th>
                                    <th class="py-3 px-3 text-center">Số lượng</th>
                                    <th class="py-3 px-3 text-right">Đơn giá</th>
                                    <th class="py-3 px-3 text-right">Thành tiền</th>
                                    <th class="py-3 px-3 text-center">Trạng thái bếp</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($orderDetails as $item)
                                    <tr class="hover:bg-amber-50/20 transition-colors">
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-2.5">
                                                @php
                                                    $pThumb = $item->product && $item->product->thumbnail 
                                                        ? asset('images/products/' . $item->product->thumbnail) 
                                                        : asset('images/default-dish.jpg');
                                                @endphp
                                                <img src="{{ $pThumb }}" alt="{{ $item->product->name ?? 'Món' }}" 
                                                     class="w-10 h-10 rounded-lg object-cover border border-gray-200 shrink-0"
                                                     onerror="this.src='/images/default-dish.jpg'">
                                                <div>
                                                    <span class="font-bold text-gray-900 block text-xs sm:text-sm">
                                                        {{ $item->product->name ?? ('Món #' . $item->product_id) }}
                                                    </span>
                                                    <span class="text-[10px] text-gray-400">Lượt gọi #{{ $item->order_turn ?? 1 }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-3 text-center font-bold text-gray-900">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-800 text-[11px] font-bold">
                                                x{{ $item->qty }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 text-right font-mono text-gray-600">
                                            {{ number_format($item->price, 0, ',', '.') }}₫
                                        </td>
                                        <td class="py-3 px-3 text-right font-mono font-bold text-gray-900">
                                            {{ number_format($item->amount > 0 ? $item->amount : ($item->price * $item->qty), 0, ',', '.') }}₫
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            @php
                                                $st = $item->status;
                                                $stClass = 'bg-amber-100 text-amber-700 border-amber-200';
                                                $stText = 'Chờ bếp';
                                                if ($st == 'cooking' || $st == '1') {
                                                    $stClass = 'bg-blue-100 text-blue-700 border-blue-200';
                                                    $stText = 'Đang nấu';
                                                } elseif ($st == 'completed' || $st == '2') {
                                                    $stClass = 'bg-emerald-100 text-emerald-700 border-emerald-200';
                                                    $stText = 'Đã lên bàn';
                                                }
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold border {{ $stClass }}">
                                                {{ $stText }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-gray-400">
                                            Chưa có món ăn nào trong đơn hàng này.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Bảng Tính Tiền & Thanh Toán -->
                    <div class="p-4 sm:p-5 bg-stone-50 border-t border-gray-100 space-y-2 text-xs">
                        <div class="flex items-center justify-between text-gray-600">
                            <span>Tạm tính các món ({{ $orderDetails->sum('qty') }} phần):</span>
                            <span class="font-bold text-gray-800 font-mono">{{ number_format($subtotal, 0, ',', '.') }}₫</span>
                        </div>

                        <div class="flex items-center justify-between text-emerald-700">
                            <span>Ưu đãi đặt bàn (-10%):</span>
                            <span class="font-bold font-mono">-{{ number_format($discountAmount, 0, ',', '.') }}₫</span>
                        </div>

                        <div class="pt-3 border-t border-gray-200 flex items-center justify-between text-sm sm:text-base">
                            <span class="font-bold text-gray-900">Tổng thanh toán:</span>
                            <span class="text-xl font-black text-[#F47716] font-mono">
                                {{ number_format($finalTotal, 0, ',', '.') }}₫
                            </span>
                        </div>
                    </div>

                    <!-- Nút Thao Tác Trực Tiếp Cho Bàn -->
                    <div class="p-4 bg-white border-t border-gray-100 flex items-center justify-between gap-3 flex-wrap">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.table-order.order', $table->id) }}" 
                               class="ant-btn ant-btn-sm font-semibold">
                                Gọi thêm món (POS)
                            </a>
                            <a href="{{ route('site.table.bill', $table->id) }}" target="_blank"
                               class="text-xs text-blue-600 hover:text-blue-800 font-medium hover:underline">
                                Xem hóa đơn điện tử
                            </a>
                        </div>

                        <a href="{{ route('admin.table-order.payment', $table->id) }}" 
                           class="ant-btn ant-btn-sm bg-emerald-600 text-white hover:bg-emerald-700 border-none font-bold shadow-xs">
                            Thanh toán bàn này
                        </a>
                    </div>
                </div>

            @else
                <!-- BÀN ĐANG TRỐNG -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-8 text-center space-y-4">
                    <div class="w-14 h-14 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center mx-auto text-xl font-bold font-mono">
                        {{ $table->seats }}
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Bàn hiện đang trống</h3>
                        <p class="text-xs text-gray-500 max-w-md mx-auto mt-1 leading-relaxed">
                            {{ $table->name }} sẵn sàng đón lượt khách mới. Khi khách ngồi, bạn có thể tạo order qua POS hoặc khách tự quét mã QR bên cạnh để gọi món.
                        </p>
                    </div>

                    <div class="pt-2">
                        <a href="{{ route('admin.table-order.order', $table->id) }}" 
                           class="ant-btn ant-btn-primary font-bold shadow-xs">
                            Mở bàn & Gọi món ngay (POS)
                        </a>
                    </div>
                </div>
            @endif

            <!-- Hướng Dẫn In Ấn & Sử Dụng Standee (Gọn gàng, không icon rườm rà) -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 text-xs text-gray-600 space-y-2.5">
                <h4 class="font-bold text-gray-800 uppercase tracking-wider text-xs">
                    Hướng dẫn in ấn & sử dụng Standee
                </h4>
                <ul class="list-disc list-inside space-y-1.5 text-gray-500 text-xs leading-relaxed">
                    <li>Bấm nút <strong>"In Standee Bàn"</strong> để in trực tiếp trên giấy A5/A6 cứng hoặc giấy ảnh cao cấp.</li>
                    <li>Bấm nút <strong>"Tải ảnh JPG"</strong> để gửi file cho xưởng in bảng Mica để bàn hoặc decal chống nước dán lên mặt bàn.</li>
                    <li>Khách hàng không cần cài ứng dụng, chỉ cần dùng Zalo, App Ngân hàng hoặc Camera điện thoại quét là dùng được ngay.</li>
                </ul>
            </div>
        </div>

        <!-- CỘT 2: THẺ STANDEE MICA ĐỂ BÀN (5 CỘT - THIẾT KẾ VỪA VẶN, KHÔNG BỊ TRÀN HAY CẮT CHỮ) -->
        <div class="lg:col-span-5 space-y-4">
            
            <!-- THẺ STANDEE MICA THỰC TẾ (Được in ra hoặc dán bàn) -->
            <div class="flex justify-center">
                <div id="standeeCard" class="w-full max-w-[340px] bg-white rounded-2xl border border-stone-300 shadow-xl p-5 text-center text-stone-900 relative overflow-hidden font-sans">
                    <!-- Viền trang trí vàng kim phong cách nhà hàng ẩm thực cao cấp -->
                    <div class="absolute inset-x-0 top-0 h-2.5 bg-gradient-to-r from-orange-600 via-amber-400 to-orange-600"></div>

                    <!-- Header Standee -->
                    <div class="pt-2 mb-2">
                        <span class="text-[11px] font-black tracking-[0.2em] text-[#C25E00] uppercase block">CIVILIZE RESTAURANT</span>
                        <h2 class="text-[11px] text-stone-500 font-bold uppercase tracking-wider mt-0.5">Ẩm Thực Phúc Thọ</h2>
                    </div>

                    <!-- Tên Bàn Nổi Bật -->
                    <div class="my-2.5 py-1.5 px-4 bg-stone-900 text-white rounded-xl inline-block shadow-sm">
                        <span class="text-[10px] font-bold text-amber-400 uppercase tracking-widest block">VỊ TRÍ BÀN</span>
                        <h3 class="text-xl font-black tracking-tight text-white mt-0.5">{{ $table->name }}</h3>
                        <span class="text-[10px] text-stone-400 font-medium">{{ $table->floor->name ?? 'Sảnh' }}</span>
                    </div>

                    <!-- Khung Mã QR Sắc Nét (Vừa vặn 220px, không bị tràn ra ngoài) -->
                    <div class="p-2.5 bg-stone-50 rounded-xl border border-stone-200 inline-block my-2 shadow-inner" id="qrcode-container">
                        {!! $qrcode !!}
                    </div>

                    <!-- Hướng Dẫn Thực Khách 3 Bước (Hiển thị đầy đủ, không bị cắt) -->
                    <div class="mt-2 space-y-1.5 text-left bg-orange-50/70 p-2.5 rounded-xl border border-orange-200 text-xs text-stone-800">
                        <div class="flex items-center gap-2 font-semibold">
                            <span class="w-4 h-4 rounded-full bg-[#C25E00] text-white flex items-center justify-center text-[10px] shrink-0 font-bold">1</span>
                            <span>Mở Camera điện thoại quét mã QR</span>
                        </div>
                        <div class="flex items-center gap-2 font-semibold">
                            <span class="w-4 h-4 rounded-full bg-[#C25E00] text-white flex items-center justify-center text-[10px] shrink-0 font-bold">2</span>
                            <span>Chọn món ngon & Gửi vào bếp</span>
                        </div>
                        <div class="flex items-center gap-2 font-semibold">
                            <span class="w-4 h-4 rounded-full bg-[#C25E00] text-white flex items-center justify-center text-[10px] shrink-0 font-bold">3</span>
                            <span>Quẹt VietQR thanh toán tại chỗ</span>
                        </div>
                    </div>

                    <!-- Khuyến Mãi Bàn -->
                    <div class="mt-3 pt-2.5 border-t border-stone-200 text-[11px] text-[#C25E00] font-black tracking-wide">
                        ƯU ĐÃI: TỰ ĐỘNG GIẢM 10% TẠI BÀN
                    </div>
                </div>
            </div>

            <!-- Hộp Đường Dẫn Gọi Món Trực Tiếp -->
            <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm space-y-3 text-xs">
                <h4 class="font-bold text-gray-800 uppercase tracking-wider text-xs">
                    Đường Dẫn Truy Cập Bàn
                </h4>
                <p class="text-gray-500 text-[11px]">
                    Khi khách quét mã QR trên bàn, trình duyệt sẽ mở URL sau:
                </p>
                <div class="p-2.5 bg-stone-50 rounded border border-stone-200 font-mono text-[11px] break-all text-blue-700">
                    <a href="{{ $url }}" target="_blank" class="hover:underline">
                        {{ $url }}
                    </a>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $url }}'); alert('Đã sao chép đường dẫn gọi món của {{ $table->name }}!');" class="ant-btn ant-btn-sm w-full font-medium">
                        Sao chép đường dẫn
                    </button>
                    <a href="{{ $url }}" target="_blank" class="ant-btn ant-btn-sm w-full font-medium text-center">
                        Mở thử trang khách quét
                    </a>
                </div>
            </div>

        </div>

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
                link.download = 'QR_Goi_Mon_Ban_{{ $table->name }}.jpg';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
            };
            
            img.src = url;
        }
    </script>

    <!-- CSS phục vụ in ấn chỉ in thẻ standee -->
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #standeeCard, #standeeCard * {
                visibility: visible;
            }
            #standeeCard {
                position: absolute;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%);
                box-shadow: none !important;
                border: 2px solid #000 !important;
            }
        }
    </style>
</x-layout-backend>
