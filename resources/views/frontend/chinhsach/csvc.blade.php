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
          <h1 class="text-3xl font-semibold text-gray-800">Chính Sách Vận Chuyển</h1>
          <p class="text-gray-600 mt-2">Thông tin về phương thức và thời gian vận chuyển</p>
        </div>
      
        <!-- Nội dung chính sách vận chuyển -->
        <div class="bg-white shadow-md rounded-lg p-6">
      
          <!-- Phương thức vận chuyển -->
          <h2 class="text-2xl font-semibold text-gray-800 mb-4">Các Phương Thức Vận Chuyển</h2>
          <p class="text-gray-700 mb-6">
            Chúng tôi cung cấp các phương thức vận chuyển sau đây để đảm bảo hàng hóa được giao đúng hẹn và an toàn.
          </p>
          
          <!-- Danh sách phương thức vận chuyển -->
          <div class="space-y-6">
            <div>
              <h3 class="text-xl font-semibold text-gray-800">Vận Chuyển Tiết Kiệm</h3>
              <p class="text-gray-700">
                Phương thức vận chuyển tiết kiệm giúp bạn tiết kiệm chi phí vận chuyển. Thời gian giao hàng thường từ 5-7 ngày làm việc.
              </p>
            </div>
      
            <div>
              <h3 class="text-xl font-semibold text-gray-800">Vận Chuyển Nhanh</h3>
              <p class="text-gray-700">
                Phương thức này giúp giao hàng nhanh chóng, trong vòng 2-3 ngày làm việc. Phù hợp với các đơn hàng cần gấp.
              </p>
            </div>
      
            <div>
              <h3 class="text-xl font-semibold text-gray-800">Vận Chuyển Hỏa Tốc</h3>
              <p class="text-gray-700">
                Giao hàng trong ngày hoặc 1 ngày làm việc, dành cho những đơn hàng cần giao gấp hoặc các sản phẩm đặc biệt.
              </p>
            </div>
          </div>
      
          <!-- Thời gian giao hàng -->
          <h2 class="text-2xl font-semibold text-gray-800 mt-12 mb-4">Thời Gian Giao Hàng</h2>
          <p class="text-gray-700 mb-6">
            Chúng tôi cam kết giao hàng đúng hẹn với thời gian cụ thể như sau:
          </p>
      
          <!-- Danh sách thời gian giao hàng -->
          <div class="space-y-4">
            <div>
              <h3 class="text-xl font-semibold text-gray-800">Khu Vực Thành Phố</h3>
              <p class="text-gray-700">Đối với các đơn hàng trong khu vực thành phố, thời gian giao hàng là từ 1-3 ngày làm việc.</p>
            </div>
      
            <div>
              <h3 class="text-xl font-semibold text-gray-800">Khu Vực Ngoài Thành Phố</h3>
              <p class="text-gray-700">Đối với các đơn hàng ngoài thành phố, thời gian giao hàng có thể kéo dài từ 3-7 ngày làm việc.</p>
            </div>
      
            <div>
              <h3 class="text-xl font-semibold text-gray-800">Miền Núi, Vùng Xa</h3>
              <p class="text-gray-700">Đối với các vùng núi và khu vực xa xôi, thời gian giao hàng có thể mất đến 10 ngày làm việc.</p>
            </div>
          </div>
      
          <!-- Chi phí vận chuyển -->
          <h2 class="text-2xl font-semibold text-gray-800 mt-12 mb-4">Chi Phí Vận Chuyển</h2>
          <p class="text-gray-700 mb-6">
            Chi phí vận chuyển sẽ phụ thuộc vào trọng lượng của sản phẩm và phương thức vận chuyển mà bạn lựa chọn.
          </p>
          
          <!-- Chi phí các phương thức -->
          <div class="space-y-6">
            <div>
              <h3 class="text-xl font-semibold text-gray-800">Phương Thức Tiết Kiệm</h3>
              <p class="text-gray-700">
                Chi phí vận chuyển tiết kiệm là 30.000đ cho các đơn hàng dưới 5kg. Các đơn hàng trên 5kg sẽ có phụ phí tùy theo trọng lượng.
              </p>
            </div>
      
            <div>
              <h3 class="text-xl font-semibold text-gray-800">Phương Thức Nhanh</h3>
              <p class="text-gray-700">
                Chi phí vận chuyển nhanh là 50.000đ cho đơn hàng dưới 5kg. Các đơn hàng trên 5kg sẽ có phụ phí thêm 10.000đ cho mỗi kg.
              </p>
            </div>
      
            <div>
              <h3 class="text-xl font-semibold text-gray-800">Phương Thức Hỏa Tốc</h3>
              <p class="text-gray-700">
                Chi phí vận chuyển hỏa tốc sẽ là 100.000đ cho đơn hàng dưới 5kg và tăng thêm 20.000đ cho mỗi kg vượt quá.
              </p>
            </div>
          </div>
      
          <!-- Chính sách hoàn trả -->
          <h2 class="text-2xl font-semibold text-gray-800 mt-12 mb-4">Chính Sách Hoàn Trả</h2>
          <p class="text-gray-700 mb-6">
            Nếu bạn nhận được sản phẩm bị hư hỏng hoặc sai sót, vui lòng liên hệ với chúng tôi trong vòng 7 ngày kể từ ngày nhận hàng để yêu cầu hoàn trả hoặc đổi hàng.
          </p>
        </div>
      </div>
      
</x-layout-frontend>