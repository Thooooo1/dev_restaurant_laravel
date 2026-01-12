<x-layout-frontend>
    <section class="bg-gray-200 py-2 px-4">
        <div class="breadcrumb flex items-center text-gray-600 text-sm container mx-auto">
            <span class="mr-4">Bạn đang ở đây:</span>
            <a href="{{ url('/') }}" class="hover:text-orange-500"> Quay lại Trang chủ</a>
            <span class="mx-2">></span>
            <span class="font-semibold text-gray-800">Đơn hàng</span>
        </div>
    </section>
    <form action="{{ route('site.checkout') }}" method="POST" class="container mx-auto py-8">
        @csrf
        <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg p-6">
            <h2 class="text-2xl font-bold mb-6 text-center">Thông tin thanh toán</h2>

            <div class="mb-4">
                <label for="fullname" class="block text-gray-700 font-medium mb-2">Họ và tên:</label>
                <input type="text" id="fullname" name="name" value="{{ Auth::user()->fullname ?? '' }}"
                    class="w-full border border-gray-300 rounded-md p-2 bg-gray-100 cursor-not-allowed">
            </div>

            <div class="mb-4">
                <label for="phone" class="block text-gray-700 font-medium mb-2">Số điện thoại:</label>
                <input type="text" id="phone" name="phone" required
                    class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring focus:ring-blue-200">
            </div>

            <div class="mb-4">
                
                <label for="email" class="block text-gray-700 font-medium mb-2">Email:</label>
                <input type="text" id="email" name="email" 
                    value="{{ Auth::user()->email ?? '' }}" 
                    class="w-full border border-gray-300 rounded-md p-2 bg-gray-100 cursor-not-allowed" readonly>
            </div>

            <div class="mb-4">
                <label for="address" class="block text-gray-700 font-medium mb-2">Địa chỉ:</label>
                <input type="text" id="address" name="address" required
                    class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring focus:ring-blue-200">
            </div>

            <div class="mb-6">
                <label for="note" class="block text-gray-700 font-medium mb-2">Ghi chú:</label>
                <textarea id="note" name="note" placeholder="Nhập ghi chú nếu có"
                    class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring focus:ring-blue-200"></textarea>
            </div>

            @if(session('table_id'))
                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded relative mb-4 text-center">
                    <strong class="font-bold">Đang gọi món tại: {{ session('table_name') }}</strong>
                    <span class="block sm:inline">Vui lòng xác nhận đơn hàng để nhà bếp chuẩn bị.</span>
                    <input type="hidden" name="payment_method" value="TaiBan">
                </div>
            @else
            <h3 class="text-xl font-bold mb-4">Phương thức thanh toán</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <label
                    class="flex items-center gap-4 bg-gray-100 p-4 rounded-md shadow-md cursor-pointer hover:bg-gray-200 w-full">
                    <input type="radio" name="payment_method" value="Momo" class="shrink-0">
                    <img src="{{asset('images/logo/momologo.png')}}" alt="Momo" class="w-10 h-10 shrink-0">
                    <span class="break-words">Momo</span>
                </label>
                <label
                    class="flex items-center gap-4 bg-gray-100 p-4 rounded-md shadow-md cursor-pointer hover:bg-gray-200 w-full">
                    <input type="radio" name="payment_method" value="ATM" class="shrink-0">
                    <img src="{{asset('images/logo/atmlogo.jpg')}}" alt="ATM" class="w-10 h-10 shrink-0">
                    <span class="break-words">ATM</span>
                </label>
                <label
                    class="flex items-center gap-4 bg-gray-100 p-4 rounded-md shadow-md cursor-pointer hover:bg-gray-200 w-full">
                    <input type="radio" name="payment_method" value="COD" class="shrink-0">
                    <img src="{{asset('images/logo/icon.png')}}" alt="COD" class="w-10 h-10 shrink-0">
                    <span class="break-words">Thanh toán khi nhận hàng</span>
                </label>
                <label
                    class="flex items-center gap-4 bg-gray-100 p-4 rounded-md shadow-md cursor-pointer hover:bg-gray-200 w-full">
                    <input type="radio" name="payment_method" value="ZaloPay" class="shrink-0">
                    <img src="{{asset('images/logo/zalopaylogo.png')}}" alt="ZaloPay" class="w-10 h-10 shrink-0">
                    <span class="break-words">ZaloPay</span>
                </label>
                <label
                    class="flex items-center gap-4 bg-gray-100 p-4 rounded-md shadow-md cursor-pointer hover:bg-gray-200 w-full">
                    <input type="radio" name="payment_method" value="ApplePay" class="shrink-0">
                    <img src="{{asset('images/logo/applepay.png')}}" alt="ApplePay" class="w-10 h-10 shrink-0">
                    <span class="break-words">ApplePay</span>
                </label>
                <label
                    class="flex items-center gap-4 bg-gray-100 p-4 rounded-md shadow-md cursor-pointer hover:bg-gray-200 w-full">
                    <input type="radio" name="payment_method" value="VNPAY" class="shrink-0">
                    <img src="{{asset('images/logo/vnpay.jpg')}}" alt="VNPAY" class="w-10 h-10 shrink-0">
                    <span class="break-words">VNPAY</span>
                </label>
            </div>
            @endif
           
            <div class="text-center mt-6">
                <button type="submit" id="ok"
                    class="bg-orange-500 text-white py-2 px-4 rounded-md hover:bg-orange-600 focus:outline-none focus:ring focus:ring-orange-300">
                    {{ session('table_id') ? 'Gửi gọi món' : 'Xác nhận' }}
                </button>
            </div>
        </div>
    </form>
    {{-- <script>
        const form = document.querySelector("form");
form.addEventListener("submit", function(event) {
    const street = document.getElementById("street").value.trim();
    const district = document.getElementById("district").value.trim();
    const city = document.getElementById("city").value.trim();

    if (!street || !district || !city) {
        alert("Vui lòng nhập đầy đủ địa chỉ!");
        event.preventDefault();  // Ngừng gửi form
        return;
    }

    const fullAddress = `${street}, ${district}, ${city}`;
    document.getElementById("address").value = fullAddress;  // Gán giá trị đầy đủ vào trường address
});

    </script> --}}
</x-layout-frontend>
