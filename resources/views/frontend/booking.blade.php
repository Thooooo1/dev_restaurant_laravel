<x-layout-frontend>
    <style>
        .font-serif-luxury {
            font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
            font-weight: 800 !important;
            letter-spacing: -0.02em;
        }
        .booking-field-input {
            background-color: #FFFFFF;
            border: 1px solid #E2E8F0;
            color: #0F172A;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            border-radius: 2px;
            font-weight: 500;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .booking-field-input:focus {
            border-color: #D97706;
            box-shadow: 0 0 0 2px rgba(217, 119, 6, 0.15);
            outline: none;
            background-color: #FFFFFF;
        }
        .booking-field-input::placeholder {
            color: #94A3B8;
            font-weight: 300;
        }
    </style>

    <!-- Main Booking Section (Nền sáng be/kem nhạt đồng bộ cùng header) -->
    <section class="min-h-[85vh] py-10 md:py-16 bg-[#FAF7F2] text-[#1E293B]">
        <div class="container mx-auto px-4 sm:px-6 max-w-3xl">
            
            <!-- Tiêu đề & Giới thiệu (Đã bỏ chữ ĐẶT BÀN TRƯỚC) -->
            <div class="mb-6 md:mb-8">
                <h1 class="font-serif-luxury text-3xl sm:text-4xl lg:text-[40px] font-bold text-[#1E293B] tracking-tight leading-tight">
                    Đặt Bàn Giữ Chỗ
                </h1>
            </div>

            <!-- Thông báo Thành công -->
            @if (session('success'))
                <div class="mb-5 p-3.5 rounded-none sm:rounded-sm bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-start gap-2.5">
                    <div class="mt-0.5 w-4 h-4 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px] shrink-0 font-bold">✓</div>
                    <div>
                        <h4 class="font-bold text-emerald-900 mb-0.5">Đặt bàn thành công!</h4>
                        <p class="text-emerald-700">{{ session('success') }} Bộ phận lễ tân của CIVILIZE sẽ liên hệ với bạn trong thời gian sớm nhất để xác nhận.</p>
                    </div>
                </div>
            @endif

            <!-- Thông báo Lỗi -->
            @if (isset($errors) && $errors->any())
                <div class="mb-5 p-3.5 rounded-none sm:rounded-sm bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm">
                    <div class="font-bold text-rose-900 mb-1">Vui lòng kiểm tra lại thông tin:</div>
                    <ul class="list-disc list-inside space-y-0.5 text-rose-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form Đặt bàn -->
            <form action="{{ route('site.booking.store') }}" method="POST" class="space-y-4">
                @csrf
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Họ và tên -->
                    <div>
                        <label class="block text-xs font-medium text-stone-700 uppercase tracking-wider mb-1">
                            Họ và tên <span class="text-[#F47716]">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               value="{{ old('name') }}"
                               placeholder="Ví dụ: Nguyễn Văn A" 
                               required 
                               class="booking-field-input w-full px-3.5 py-2.5 text-sm" />
                    </div>

                    <!-- Số điện thoại -->
                    <div>
                        <label class="block text-xs font-medium text-stone-700 uppercase tracking-wider mb-1">
                            Số điện thoại <span class="text-[#F47716]">*</span>
                        </label>
                        <input type="tel" 
                               name="phone" 
                               value="{{ old('phone') }}"
                               placeholder="Ví dụ: 0964 358 394" 
                               required 
                               class="booking-field-input w-full px-3.5 py-2.5 text-sm" />
                    </div>

                    <!-- Email xác nhận -->
                    <div>
                        <label class="block text-xs font-medium text-stone-700 uppercase tracking-wider mb-1">
                            Email xác nhận <span class="text-[#F47716]">*</span>
                        </label>
                        <input type="email" 
                               name="email" 
                               value="{{ old('email') }}"
                               placeholder="name@example.com" 
                               required 
                               class="booking-field-input w-full px-3.5 py-2.5 text-sm" />
                    </div>

                    <!-- Số lượng khách -->
                    <div>
                        <label class="block text-xs font-medium text-stone-700 uppercase tracking-wider mb-1">
                            Số lượng khách <span class="text-[#F47716]">*</span>
                        </label>
                        <input type="number" 
                               name="number_of_people" 
                               value="{{ old('number_of_people', 2) }}"
                               min="1" 
                               max="100"
                               required 
                               class="booking-field-input w-full px-3.5 py-2.5 text-sm" />
                    </div>

                    <!-- Ngày dùng bữa -->
                    <div>
                        <label class="block text-xs font-medium text-stone-700 uppercase tracking-wider mb-1">
                            Ngày dùng bữa <span class="text-[#F47716]">*</span>
                        </label>
                        <input type="date" 
                               name="booking_date" 
                               value="{{ old('booking_date', date('Y-m-d')) }}"
                               required 
                               class="booking-field-input w-full px-3.5 py-2.5 text-sm" />
                    </div>

                    <!-- Giờ dùng bữa -->
                    <div>
                        <label class="block text-xs font-medium text-stone-700 uppercase tracking-wider mb-1">
                            Giờ dùng bữa <span class="text-[#F47716]">*</span>
                        </label>
                        <input type="time" 
                               name="booking_time" 
                               value="{{ old('booking_time', '18:30') }}"
                               required 
                               class="booking-field-input w-full px-3.5 py-2.5 text-sm" />
                    </div>
                </div>

                <!-- Ghi chú đặc biệt -->
                <div>
                    <label class="block text-xs font-medium text-stone-700 uppercase tracking-wider mb-1">
                        Ghi chú đặc biệt
                    </label>
                    <textarea name="note" 
                              rows="3" 
                              placeholder="Yêu cầu vị trí ngồi (phòng riêng / sảnh chung), tiệc sinh nhật, ghế em bé, set up hoa hoặc lưu ý món ăn..." 
                              class="booking-field-input w-full px-3.5 py-2.5 text-sm resize-none">{{ old('note') }}</textarea>
                </div>

                <!-- Nút Bấm Chính: Phong cách nhà hàng sang trọng, góc vuông tinh tế -->
                <div class="pt-2">
                    <button type="submit" 
                            class="w-full py-4 rounded-none sm:rounded-sm bg-stone-900 hover:bg-[#C25E00] text-white font-bold text-xs uppercase tracking-[0.2em] transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer">
                        Xác Nhận Đặt Bàn Ngay
                    </button>
                </div>
            </form>

            <!-- Dòng cam kết duy nhất căn giữa dưới nút bấm -->
            <div class="mt-4 text-center">
                <p class="text-xs text-stone-500 font-light">
                    Nhà hàng sẽ liên hệ xác nhận trong vòng 15 phút qua điện thoại.
                </p>
            </div>

        </div>
    </section>
</x-layout-frontend>