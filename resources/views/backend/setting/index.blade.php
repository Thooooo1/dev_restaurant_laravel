<x-layout-backend>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-[#F0F0F0]">
            <div>
                <h1 class="text-xl font-bold text-slate-800 tracking-tight">
                    Cài đặt hệ thống
                </h1>
            </div>
            <div>
                <button type="submit" form="settingForm" class="ant-btn ant-btn-primary shadow-sm">
                    Lưu cài đặt
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded text-xs flex items-center gap-2">
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <form id="settingForm" action="{{ route('admin.setting.update') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- 1. Thông tin chung Nhà Hàng -->
                <div class="bg-white border border-[#F0F0F0] rounded p-5 shadow-xs space-y-4">
                    <div class="pb-3 border-b border-[#F0F0F0]">
                        <h2 class="text-sm font-bold text-[rgba(0,0,0,0.85)]">1. Thông tin Nhà hàng & Liên hệ</h2>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Tên nhà hàng <span class="text-red-500">*</span></label>
                            <input type="text" name="restaurant_name" value="{{ old('restaurant_name', $settings['restaurant_name'] ?? '') }}" required
                                   class="w-full h-8 px-3 rounded border border-gray-300 focus:border-[#F47716] focus:outline-none transition-colors">
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Khẩu hiệu (Slogan)</label>
                            <input type="text" name="restaurant_slogan" value="{{ old('restaurant_slogan', $settings['restaurant_slogan'] ?? '') }}"
                                   class="w-full h-8 px-3 rounded border border-gray-300 focus:border-[#F47716] focus:outline-none transition-colors">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-gray-700 mb-1">Hotline CSKH / Đặt bàn <span class="text-red-500">*</span></label>
                                <input type="text" name="hotline" value="{{ old('hotline', $settings['hotline'] ?? '') }}" required
                                       class="w-full h-8 px-3 rounded border border-gray-300 focus:border-[#F47716] focus:outline-none transition-colors">
                            </div>
                            <div>
                                <label class="block font-semibold text-gray-700 mb-1">Email liên hệ</label>
                                <input type="email" name="email" value="{{ old('email', $settings['email'] ?? '') }}"
                                       class="w-full h-8 px-3 rounded border border-gray-300 focus:border-[#F47716] focus:outline-none transition-colors">
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Địa chỉ nhà hàng <span class="text-red-500">*</span></label>
                            <input type="text" name="address" value="{{ old('address', $settings['address'] ?? '') }}" required
                                   class="w-full h-8 px-3 rounded border border-gray-300 focus:border-[#F47716] focus:outline-none transition-colors">
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Thời gian phục vụ hàng ngày</label>
                            <input type="text" name="opening_hours" value="{{ old('opening_hours', $settings['opening_hours'] ?? '') }}"
                                   class="w-full h-8 px-3 rounded border border-gray-300 focus:border-[#F47716] focus:outline-none transition-colors">
                        </div>
                    </div>
                </div>

                <!-- 2. Cấu hình Thanh toán VietQR & Trực tuyến -->
                <div class="bg-white border border-[#F0F0F0] rounded p-5 shadow-xs space-y-4">
                    <div class="pb-3 border-b border-[#F0F0F0]">
                        <h2 class="text-sm font-bold text-[rgba(0,0,0,0.85)]">2. Cấu hình Thanh toán & VietQR Chuyển khoản</h2>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-gray-700 mb-1">Mã ngân hàng (VietQR BIN) <span class="text-red-500">*</span></label>
                                <select name="bank_id" class="w-full h-8 px-3 rounded border border-gray-300 focus:border-[#F47716] focus:outline-none transition-colors bg-white">
                                    <option value="MB" {{ ($settings['bank_id'] ?? '') == 'MB' ? 'selected' : '' }}>MB - MBBank (Quân Đội)</option>
                                    <option value="VCB" {{ ($settings['bank_id'] ?? '') == 'VCB' ? 'selected' : '' }}>VCB - Vietcombank</option>
                                    <option value="TCB" {{ ($settings['bank_id'] ?? '') == 'TCB' ? 'selected' : '' }}>TCB - Techcombank</option>
                                    <option value="ACB" {{ ($settings['bank_id'] ?? '') == 'ACB' ? 'selected' : '' }}>ACB - Á Châu</option>
                                    <option value="BIDV" {{ ($settings['bank_id'] ?? '') == 'BIDV' ? 'selected' : '' }}>BIDV - Đầu tư & Phát triển VN</option>
                                    <option value="CTG" {{ ($settings['bank_id'] ?? '') == 'CTG' ? 'selected' : '' }}>CTG - VietinBank</option>
                                    <option value="VPB" {{ ($settings['bank_id'] ?? '') == 'VPB' ? 'selected' : '' }}>VPB - VPBank</option>
                                    <option value="TPB" {{ ($settings['bank_id'] ?? '') == 'TPB' ? 'selected' : '' }}>TPB - TPBank</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold text-gray-700 mb-1">Tên hiển thị ngân hàng</label>
                                <input type="text" name="bank_name" value="{{ old('bank_name', $settings['bank_name'] ?? '') }}"
                                       class="w-full h-8 px-3 rounded border border-gray-300 focus:border-[#F47716] focus:outline-none transition-colors">
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Số tài khoản nhận tiền <span class="text-red-500">*</span></label>
                            <input type="text" name="bank_account_no" value="{{ old('bank_account_no', $settings['bank_account_no'] ?? '') }}" required
                                   class="w-full h-8 px-3 rounded border border-gray-300 focus:border-[#F47716] focus:outline-none font-mono text-sm transition-colors">
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Tên chủ tài khoản (Viết hoa không dấu) <span class="text-red-500">*</span></label>
                            <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $settings['bank_account_name'] ?? '') }}" required
                                   class="w-full h-8 px-3 rounded border border-gray-300 focus:border-[#F47716] focus:outline-none font-mono uppercase transition-colors">
                        </div>

                        <div class="pt-2 border-t border-gray-100">
                            <span class="block font-semibold text-gray-700 mb-2">Các phương thức thanh toán hỗ trợ:</span>
                            <div class="grid grid-cols-3 gap-2">
                                <label class="flex items-center gap-2 p-2 border rounded border-gray-200 cursor-pointer hover:bg-gray-50">
                                    <input type="checkbox" name="enable_vietqr" value="1" {{ !empty($settings['enable_vietqr']) ? 'checked' : '' }} class="rounded text-[#F47716] focus:ring-0">
                                    <span class="font-medium text-gray-800">VietQR Chuyển khoản</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 border rounded border-gray-200 cursor-pointer hover:bg-gray-50">
                                    <input type="checkbox" name="enable_cash" value="1" {{ !empty($settings['enable_cash']) ? 'checked' : '' }} class="rounded text-[#F47716] focus:ring-0">
                                    <span class="font-medium text-gray-800">Tiền mặt tại quầy</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 border rounded border-gray-200 cursor-pointer hover:bg-gray-50">
                                    <input type="checkbox" name="enable_momo" value="1" {{ !empty($settings['enable_momo']) ? 'checked' : '' }} class="rounded text-[#F47716] focus:ring-0">
                                    <span class="font-medium text-gray-800">Ví MoMo</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Bán hàng & Khuyến mãi đặt bàn -->
                <div class="bg-white border border-[#F0F0F0] rounded p-5 shadow-xs space-y-4">
                    <div class="pb-3 border-b border-[#F0F0F0]">
                        <h2 class="text-sm font-bold text-[rgba(0,0,0,0.85)]">3. Chính sách Bán hàng & Đặt bàn Online</h2>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Ưu đãi giảm giá khi khách quét QR đặt món tại bàn (%)</label>
                            <div class="flex items-center gap-2">
                                <input type="number" min="0" max="100" name="table_booking_discount_percent" value="{{ old('table_booking_discount_percent', $settings['table_booking_discount_percent'] ?? 10) }}"
                                       class="w-32 h-8 px-3 rounded border border-gray-300 focus:border-[#F47716] focus:outline-none transition-colors">
                                <span class="text-gray-500 font-medium">%</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-gray-700 mb-1">Phí giao hàng cơ bản (VNĐ)</label>
                                <input type="number" min="0" step="1000" name="shipping_base_fee" value="{{ old('shipping_base_fee', $settings['shipping_base_fee'] ?? 25000) }}"
                                       class="w-full h-8 px-3 rounded border border-gray-300 focus:border-[#F47716] focus:outline-none transition-colors">
                            </div>
                            <div>
                                <label class="block font-semibold text-gray-700 mb-1">Miễn phí ship với đơn từ (VNĐ)</label>
                                <input type="number" min="0" step="50000" name="shipping_free_threshold" value="{{ old('shipping_free_threshold', $settings['shipping_free_threshold'] ?? 500000) }}"
                                       class="w-full h-8 px-3 rounded border border-gray-300 focus:border-[#F47716] focus:outline-none transition-colors">
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Bán kính giao hàng tối đa (km)</label>
                            <input type="number" min="1" max="100" name="max_delivery_distance_km" value="{{ old('max_delivery_distance_km', $settings['max_delivery_distance_km'] ?? 15) }}"
                                   class="w-32 h-8 px-3 rounded border border-gray-300 focus:border-[#F47716] focus:outline-none transition-colors">
                        </div>
                    </div>
                </div>

                <!-- 4. Chuông thông báo & Nhắc nhở thời gian thực -->
                <div class="bg-white border border-[#F0F0F0] rounded p-5 shadow-xs space-y-4">
                    <div class="pb-3 border-b border-[#F0F0F0]">
                        <h2 class="text-sm font-bold text-[rgba(0,0,0,0.85)]">4. Chuông Thông Báo & Nhắc Nhở Nhân Viên</h2>
                    </div>

                    <div class="space-y-3 text-xs">
                        <label class="flex items-center gap-3 p-3 rounded border border-gray-200 cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="sound_service_call" value="1" {{ !empty($settings['sound_service_call']) ? 'checked' : '' }} class="rounded text-[#F47716] focus:ring-0">
                            <span class="font-semibold text-gray-800">Âm thanh chuông khi khách gọi nhân viên (Ding-Dong)</span>
                        </label>

                        <label class="flex items-center gap-3 p-3 rounded border border-gray-200 cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="sound_new_order" value="1" {{ !empty($settings['sound_new_order']) ? 'checked' : '' }} class="rounded text-[#F47716] focus:ring-0">
                            <span class="font-semibold text-gray-800">Âm thanh khi có món mới hoặc đơn hàng mới</span>
                        </label>

                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Tần số quét dữ liệu thời gian thực (giây)</label>
                            <div class="flex items-center gap-2">
                                <input type="number" min="2" max="60" name="polling_interval_seconds" value="{{ old('polling_interval_seconds', $settings['polling_interval_seconds'] ?? 5) }}"
                                       class="w-28 h-8 px-3 rounded border border-gray-300 focus:border-[#F47716] focus:outline-none transition-colors">
                                <span class="text-gray-500 font-medium">giây</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-[#F0F0F0]">
                <a href="{{ route('admin.dashboard') }}" class="ant-btn">Quay lại Dashboard</a>
                <button type="submit" class="ant-btn ant-btn-primary shadow-sm">
                    Lưu cài đặt hệ thống
                </button>
            </div>
        </form>
    </div>
</x-layout-backend>
