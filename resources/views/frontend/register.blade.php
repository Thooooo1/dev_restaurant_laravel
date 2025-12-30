<x-layout-frontend>
    <section class="bg-gray-200 ml-5">
        <div class="breadcrumb flex items-center text-gray-600 text-sm  ">
            <span class="mr-4">Bạn đang ở đây:</span>
            <a href="{{url('/')}}" class="hover:text-orange-500"> Quay lại Trang chủ</a>
            <span class="mx-2">></span>
            <span class="font-semibold text-gray-800">Đăng kí</span>
        </div>
    </section>
    <div class="max-w-md mx-auto bg-white p-8 border border-gray-200 rounded-lg shadow my-12">
        @include("components.alert")
        <header class="mb-6 text-center">
            <h4 class="text-2xl font-semibold">Đăng ký</h4>
        </header>
        <form action="{{route('site.doregister')}}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class=" mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Họ tên</label>
                    <input 
                        name="fullname"
                        type="text" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:outline-none" 
                        placeholder=""
                    />
                </div>
                
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tên người dùng</label>
                <input 
                    name="username"
                    type="text" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:outline-none" 
                    placeholder=""
                />
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Địa chỉ</label>
                <input 
                    name="address"
                    type="text" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:outline-none" 
                    placeholder=""
                />
            </div>
           
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input 
                    name="email"
                    type="email" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:outline-none" 
                    placeholder=""
                />
                <p class="text-xs text-gray-500 mt-1">Email sẽ được bảo mật.</p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại</label>
                <input 
                    name="phone"
                    type="text" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:outline-none" 
                    placeholder=""
                />
                
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Giới tính</label>
                <div class="flex items-center space-x-4">
                    <label><input type="radio" name="gender" value="Nam" class="mr-1"> Nam</label>
                    <label><input type="radio" name="gender" value="Nữ" class="mr-1"> Nữ</label>
                    {{-- <label><input type="radio" name="gender" value="Khác" class="mr-1"> Khác</label> --}}
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tạo mật khẩu</label>
                    <input 
                        name="password"
                        type="password" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:outline-none" 
                        placeholder=""
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nhập lại mật khẩu</label>
                    <input 
                        type="password" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:outline-none" 
                        placeholder=""
                    />
                </div>
                <div class="mb-3">
                    <label for="image">Hình ảnh</label>
                    <input type="file" name="image" id="image" class="form-control" onchange="previewImage(event)">
                    <img id="preview" class="mt-3 w-32 h-32 object-cover rounded-md" style="display: none;" />
                </div>
            </div>
            <div class="mb-4">
                <button 
                    type="submit" 
                    class="w-full bg-orange-500 text-white py-2 px-4 rounded-md hover:bg-orange-600 focus:outline-none"
                >
                    Đăng kí
                </button>
            </div>
        </form>
        <hr class="my-6">
        <p class="text-center text-sm">
            Bạn đã có tài khoản ? 
            <a href="{{url('/dang-nhap')}}" class="text-blue-500 hover:underline">Đăng nhập</a>
        </p>
    </div>
    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('preview');
                output.src = reader.result;
                output.style.display = 'block';
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
    
</x-layout-frontend>
