<x-layout-frontend>
    <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-lg my-12">
        <h4 class="text-center text-2xl font-semibold mb-4">Xác nhận OTP</h4>
        <form action="{{ route('site.verify_otp') }}" method="POST">
            @csrf
            <input type="hidden" name="email" value="{{ old('email', $email) }}">
            <div class="mb-4">
                <label for="otp_code" class="block text-sm font-medium text-gray-700 mb-2">Mã OTP</label>
                <input type="text" name="otp_code" class="w-full px-4 py-2 border border-gray-300 rounded-md" placeholder="Nhập mã OTP">
            </div>
            <button type="submit" class="w-full bg-orange-500 text-white py-2 px-4 rounded-md hover:bg-orange-600">
                Xác nhận
            </button>
        </form>
        
    </div>
</x-layout-frontend>
