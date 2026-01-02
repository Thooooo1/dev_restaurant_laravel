<x-layout-frontend>
  <section class="bg-gray-200 ml-5">
    <div class="breadcrumb flex items-center text-gray-600 text-sm  ">
        <span class="mr-4">Bạn đang ở đây:</span>
        <a href="{{url('/')}}" class="hover:text-orange-500"> Quay lại Trang chủ</a>
        <span class="mx-2">></span>
        <span class="font-semibold text-gray-800">Đăng nhập</span>
    </div>
</section>
<div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-lg my-12">
        <div class="text-center mb-4">  
          <h4 class="text-2xl font-semibold">Đăng nhập</h4>
        </div>
        <a onclick="return alert('Chức năng đang trong quá trình phát triển')" class="w-full flex items-center justify-center bg-blue-600 text-white py-2 px-4 rounded-md mb-2 hover:bg-blue-700">
          <i class="fab fa-facebook-f mr-2"></i> Đăng nhập với FaceBook
        </a>
        <a href="{{ route('google.redirect') }}" class="w-full flex items-center justify-center bg-red-600 text-white py-2 px-4 rounded-md mb-4 hover:bg-red-700">
          <i class="fab fa-google mr-2"></i> Đăng nhập với Google
        </a>     
        <form action="{{route('site.dologin')}}" method="POST">
          @csrf
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Số điện thoại</label>
            <input type="text" name="phone" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" placeholder="ex. 0123456789">
          </div>
          
          <div class="mb-4">
            <div class="flex justify-between">
              <label class="text-sm font-medium text-gray-700 mb-2">Mật khẩu</label>
              <a href="{{route('site.forgot_password')}}" class="text-sm text-blue-500 hover:underline">Quên mật khẩu?</a>
            </div>
            <input type="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" placeholder="******">
          </div>
      
          <div class="mb-4 flex items-center">
            <input type="checkbox" class="h-4 w-4 text-blue-500 border-gray-300 rounded" checked>
            <label class="ml-2 text-sm text-gray-700">Ghi nhớ</label>
          </div>
      
          <div class="mb-4">
            <button type="submit" class="w-full bg-orange-500 text-white py-2 px-4 rounded-md hover:bg-orange-600 focus:outline-none">
              Đăng nhập
            </button>
          </div>
          <p class="text-center text-sm">
            Bạn chưa có tài khoản ? 
            <a href="{{url('/dang-ky')}}" class="text-blue-500 hover:underline">Đăng ký</a>
        </p>
        </form>
      </div>
          
</x-layout-frontend>