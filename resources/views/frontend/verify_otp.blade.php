<x-layout-frontend>
    <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-lg my-12 mx-4 md:mx-auto">
        <h4 class="text-center text-2xl font-semibold mb-4">Xác nhận OTP</h4>

        {{-- HIỂN THỊ OTP TO RÕ, LUÔN Ở ĐÂY --}}
        @if (isset($otp_display))
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-4 rounded relative mb-6 text-center">
                <div class="text-lg font-semibold mb-2">MÃ OTP CỦA BẠN LÀ:</div>
                <div class="text-4xl font-bold tracking-widest" style="letter-spacing: 8px;">{{ $otp_display }}</div>
                <div class="text-sm mt-2">Hiệu lực trong 10 phút</div>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('site.verify_otp') }}" method="POST">
            @csrf
            <input type="hidden" name="phone" value="{{ old('phone', $phone) }}">
            <div class="mb-4">
                <label for="otp_code" class="block text-sm font-medium text-gray-700 mb-2">Nhập mã OTP</label>
                <input type="text" name="otp_code" class="w-full px-4 py-2 border border-gray-300 rounded-md text-center text-2xl tracking-widest" placeholder="000000" maxlength="6" value="{{ old('otp_code') }}">
                @error('otp_code')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="w-full bg-orange-500 text-white py-2 px-4 rounded-md hover:bg-orange-600">
                Xác nhận
            </button>
        </form>
        
    </div>
</x-layout-frontend>
