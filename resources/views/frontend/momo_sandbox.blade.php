<x-layout-frontend>
    <main class="bg-[#FAF8F5] min-h-screen pb-16">
        <x-page-hero 
            title="Cổng Thanh Toán MoMo" 
            subtitle="Giao dịch thanh toán trực tuyến bảo mật thông qua ví điện tử MoMo." 
            :breadcrumbs="[
                ['name' => 'Giỏ hàng', 'url' => route('site.cart')],
                ['name' => 'Thanh toán MoMo', 'url' => '']
            ]" 
        />

        <div class="container mx-auto px-4 max-w-xl pt-8 sm:pt-12">
            <div class="bg-white rounded-none sm:rounded-sm shadow-md overflow-hidden border border-stone-200">
                <!-- Header MoMo -->
                <div class="bg-gradient-to-r from-[#a50064] to-[#d82d8b] p-6 text-white text-center relative">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-none sm:rounded-sm shadow-md p-2 mb-3">
                        <img src="{{ asset('images/logo/momologo.png') }}" alt="MoMo" class="w-full h-full object-contain">
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black tracking-wide font-serif">CỔNG THANH TOÁN VÍ MOMO</h1>
                    <p class="text-xs text-pink-100 mt-1">Hệ thống thanh toán trực tuyến Nhà hàng CIVILIZE</p>
                    <div class="inline-block mt-2.5 px-3 py-1 bg-white/20 rounded-none sm:rounded-sm text-[11px] font-semibold text-white">
                        <i class="fas fa-shield-alt mr-1"></i> Giao dịch bảo mật 256-bit SSL
                    </div>
                </div>

                <!-- Body Details -->
                <div class="p-6 sm:p-8">
                    <!-- Thông tin đơn hàng -->
                    <div class="bg-stone-50 rounded-none sm:rounded-sm p-4 border border-stone-200 mb-6">
                        <div class="flex justify-between items-center pb-3 border-b border-stone-200/80">
                            <span class="text-xs text-stone-500 uppercase tracking-wider font-semibold">Mã đơn hàng</span>
                            <span class="font-bold text-stone-800 text-sm">#{{ $order->id }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2.5 border-b border-stone-200/80">
                            <span class="text-xs text-stone-500 uppercase tracking-wider font-semibold">Khách hàng</span>
                            <span class="font-semibold text-stone-800 text-sm">{{ $order->name }} ({{ $order->phone }})</span>
                        </div>
                        <div class="flex justify-between items-center py-2.5 border-b border-stone-200/80">
                            <span class="text-xs text-stone-500 uppercase tracking-wider font-semibold">Địa chỉ nhận</span>
                            <span class="text-xs text-stone-700 text-right max-w-[60%] line-clamp-2">{{ $order->address }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-3">
                            <span class="text-xs text-stone-500 uppercase tracking-wider font-semibold">Tổng số tiền cần trả</span>
                            <span class="text-2xl font-black text-[#a50064]">{{ number_format($order->total_price, 0, ',', '.') }}₫</span>
                        </div>
                    </div>

                    <!-- QR Code Thanh toán -->
                    <div class="text-center mb-6">
                        <div class="inline-block p-3.5 bg-white border-2 border-dashed border-[#d82d8b] rounded-none sm:rounded-sm shadow-2xs">
                            @php
                                $qrAmount = (int)($order->total_price ?? 50000);
                                $qrContent = "MOMO{$order->id}";
                                $qrUrl = "https://api.vietqr.io/image/970422-0338990538-t9pT2pB.jpg?amount={$qrAmount}&addInfo={$qrContent}&accountName=CIVILIZE";
                            @endphp
                            <img src="{{ $qrUrl }}" alt="Mã QR Thanh toán MoMo" class="w-56 h-56 mx-auto object-contain rounded-none sm:rounded-sm">
                        </div>
                        <p class="text-xs text-stone-500 mt-2.5 font-medium">
                            <i class="fas fa-qrcode text-[#a50064] mr-1"></i> Mở App <strong>MoMo</strong> hoặc App <strong>Ngân hàng</strong> bất kỳ để quét mã QR
                        </p>
                        <p class="text-[11px] text-stone-400 mt-1">
                            Nội dung chuyển khoản: <span class="font-bold text-stone-700">MOMO{{ $order->id }}</span>
                        </p>
                    </div>

                    <!-- Nút xác nhận thanh toán -->
                    <form action="{{ route('site.momo.sandbox.confirm', ['order' => $order->id]) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full bg-[#a50064] hover:bg-[#880052] text-white font-bold py-3.5 px-6 rounded-none sm:rounded-sm shadow-md transition duration-200 flex items-center justify-center gap-2 cursor-pointer text-xs uppercase tracking-[0.15em]">
                            <i class="fas fa-check-circle"></i> Xác nhận thanh toán MoMo thành công
                        </button>
                    </form>

                    <div class="mt-4 text-center">
                        <a href="{{ route('site.cart') }}" class="text-xs text-stone-400 hover:text-stone-600 transition inline-flex items-center gap-1">
                            <i class="fas fa-arrow-left text-[10px]"></i> Hủy giao dịch & quay lại giỏ hàng
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</x-layout-frontend>
