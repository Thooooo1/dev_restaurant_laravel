<x-layout-frontend>
    <section class="bg-gray-200 py-8 px-4">
        <div class="container mx-auto">
            <div class="max-w-md mx-auto bg-white shadow-lg rounded-lg p-8">
                <h2 class="text-2xl font-bold text-center mb-6">Xác thực thông tin bàn</h2>
                
                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-blue-700 font-semibold">Bạn đang quét QR của: <span class="text-blue-900">{{ $table->name }}</span></p>
                </div>

                <form action="{{ route('site.table.verify', $table->id) }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="phone" class="block text-gray-700 font-medium mb-2">Số điện thoại đã đăng ký với lễ tân:</label>
                        <input type="text" id="phone" name="phone" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
                            placeholder="Nhập số điện thoại của bạn">
                    </div>

                    <div class="text-center mt-6">
                        <button type="submit" class="bg-orange-500 text-white py-3 px-8 rounded-md hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-300 w-full">
                            Xác thực và gọi món
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</x-layout-frontend>