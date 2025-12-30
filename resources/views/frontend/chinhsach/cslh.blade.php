<x-layout-frontend>
    <section class="bg-gray-200 ml-5">
        <div class="breadcrumb flex items-center text-gray-600 text-sm  ">
            <span class="mr-4">Bạn đang ở đây:</span>
            <a href="{{url('/')}}" class="hover:text-orange-500"> Quay lại Trang chủ</a>
            <span class="mx-2">></span>
            <span class="font-semibold text-gray-800">Chính sách</span>
        </div>
    </section>
    <div class="container mx-auto py-8 px-4">
        <!-- Tiêu đề -->
        <div class="text-center mb-12">
          <h1 class="text-3xl font-semibold text-gray-800">Chính Sách Liên Hệ</h1>
          <p class="text-gray-600 mt-2">Chúng tôi luôn sẵn sàng hỗ trợ bạn mọi lúc, mọi nơi</p>
        </div>
      
        <!-- Nội dung chính sách liên hệ -->
        <div class="bg-white shadow-md rounded-lg p-6">
      
          <!-- Địa chỉ liên hệ -->
          <h2 class="text-2xl font-semibold text-gray-800 mb-4">Thông Tin Liên Hệ</h2>
          <p class="text-gray-700 mb-6">
            Bạn có thể liên hệ với chúng tôi qua các phương thức dưới đây:
          </p>
          
          <!-- Địa chỉ văn phòng -->
          <div class="mb-6">
            <h3 class="text-xl font-semibold text-gray-800">Địa Chỉ Văn Phòng</h3>
            <p class="text-gray-700">
              VanHai Restaurant<br>
              212 Tăng Nhơn Phú, Phước Long B, Quận 9, Thành phố Thủ Đức
            </p>
          </div>
      
          <!-- Số điện thoại -->
          <div class="mb-6">
            <h3 class="text-xl font-semibold text-gray-800">Số Điện Thoại</h3>
            <p class="text-gray-700">
              Hotline: <a href="tel:+840981487674" class="text-blue-500">+84 0981487674</a><br>
              Hỗ trợ khách hàng: <a href="tel:+840981487674" class="text-blue-500">+84 0981487674</a>
            </p>
          </div>
      
          <!-- Email liên hệ -->
          <div class="mb-6">
            <h3 class="text-xl font-semibold text-gray-800">Email</h3>
            <p class="text-gray-700">
              Email hỗ trợ khách hàng: <a href="mailto:vanhai@gmail.com" class="text-blue-500">vanhai@gmail.com</a><br>
              Email bán hàng: <a href="mailto:vanhai@gmail.com" class="text-blue-500">vanhai@gmail.com</a>
            </p>
          </div>
      
          <!-- Mẫu liên hệ -->
          <h2 class="text-2xl font-semibold text-gray-800 mb-4">Gửi Yêu Cầu Liên Hệ</h2>
          <form action="#" method="POST">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              
              <!-- Tên -->
              <div class="form-group">
                <label for="name" class="block text-gray-700">Họ Tên</label>
                <input type="text" id="name" class="form-control w-full p-3 border border-gray-300 rounded-md" placeholder="Nhập họ tên">
              </div>
              
              <!-- Email -->
              <div class="form-group">
                <label for="email" class="block text-gray-700">Email</label>
                <input type="email" id="email" class="form-control w-full p-3 border border-gray-300 rounded-md" placeholder="Nhập email của bạn">
              </div>
            </div>
      
            <!-- Nội dung -->
            <div class="form-group mt-6">
              <label for="message" class="block text-gray-700">Nội Dung</label>
              <textarea id="message" rows="4" class="form-control w-full p-3 border border-gray-300 rounded-md" placeholder="Nhập nội dung yêu cầu của bạn"></textarea>
            </div>
      
            <!-- Nút gửi -->
            <div class="mt-6">
              <button type="submit" class="w-full bg-orange-500 text-white py-3 px-4 rounded-md hover:bg-orange-600 focus:outline-none">
                Gửi Yêu Cầu
              </button>
            </div>
          </form>
        </div>
      </div>
      
</x-layout-frontend>