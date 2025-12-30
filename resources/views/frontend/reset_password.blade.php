<x-layout-frontend>
    <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-lg my-12">
        <h4 class="text-center text-2xl font-semibold mb-4">Đặt lại mật khẩu</h4>
        <form action="{{ route('site.reset_password') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Mật khẩu mới</label>
                <input type="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-md" placeholder="Nhập mật khẩu mới">
            </div>
            <div class="mb-4">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Xác nhận mật khẩu</label>
                <input type="password" name="password_confirmation" class="w-full px-4 py-2 border border-gray-300 rounded-md" placeholder="Nhập lại mật khẩu mới">
            </div>
            <button type="submit" class="w-full bg-orange-500 text-white py-2 px-4 rounded-md hover:bg-orange-600">
                Đặt lại mật khẩu
            </button>
        </form>
    </div>
</x-layout-frontend>
