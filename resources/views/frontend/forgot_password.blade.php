<x-layout-frontend>
    <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-lg my-12 sm:my-20 sm:p-8">
        <h4 class="text-center text-2xl font-semibold mb-4">Quên mật khẩu</h4>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('site.send_otp') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Số điện thoại</label>
                <input 
                    type="text" 
                    name="phone" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-md" 
                    placeholder="0901234567" 
                    value="{{ old('phone') }}">
                @error('phone')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button 
                id="send-otp-btn"
                type="submit" 
                class="w-full bg-orange-500 text-white py-2 px-4 rounded-md hover:bg-orange-600">
                Lấy mã OTP
            </button>

        </form>
    </div>
</x-layout-frontend>
