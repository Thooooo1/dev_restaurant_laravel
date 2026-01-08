<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIVILIZE Restaurant</title>
    <link rel="shortcut icon" type="image/x-icon" href="../assets/images/logo/logo.png">
    <script src="{{asset('plugins/jquery/jquery.min.js')}}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">
    <!-- Thêm Quill CSS -->
    {{-- <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <!-- Thêm Quill JS -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>  --}}
    

    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-lg my-12">
    @include("components.alert")
    <div class="text-center mb-4">
        <h4 class="text-2xl font-semibold">Trang quản trị</h4>
    </div>
    <form action="{{route('admin.dologin')}}" method="POST">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Số điện thoại</label>
            <input type="text" name="phone" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" placeholder="ex. 0912345678">
        </div>

        <div class="mb-4">
            <div class="flex justify-between">
                <label class="text-sm font-medium text-gray-700 mb-2">Mật khẩu</label>
            </div>
            <input type="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" placeholder="******">
        </div>
        <div class="mb-4">
            <button type="submit" class="w-full bg-orange-500 text-white py-2 px-4 rounded-md hover:bg-orange-600 focus:outline-none">
                Đăng nhập
            </button>
        </div>        
    </form>
</div>

