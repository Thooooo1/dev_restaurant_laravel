<x-layout-frontend>
    <section class="bg-gray-200 ml-5">
        <div class="breadcrumb flex items-center text-gray-600 text-sm">
            <span class="mr-4">Bạn đang ở đây:</span>
            <a href="{{ url('/') }}" class="hover:text-orange-500">Quay lại Trang chủ</a>
            <span class="mx-2">></span>
            <a href="{{ url('/bai-viet') }}" class="hover:text-orange-500">Bài viết</a>
            <span class="mx-2">></span>
            <span class="font-semibold text-gray-800">{{ $post->title }}</span>
        </div>
    </section>

    <div class="container mx-auto my-8 px-4">
        <h1 class="text-4xl font-bold text-center mb-6 text-green-700">{{ $post->title }}</h1>       
        <p class="text-gray-600 text-center text-lg mb-4">Ngày đăng: {{ \Carbon\Carbon::parse($post->created_at)->format('d-m-Y') }}</p>      
            <p class="text-gray-500 text-center">{{ $post->description }}</p>
        </div>
        <div class="mb-8">
            <img class="w-full max-w-4xl h-auto rounded-lg shadow-lg mx-auto" 
                 src="{{ asset('images/post/' . $post->image) }}" 
                 alt="{{ $post->title }}">
        </div>
        <div class="mb-8 bg-white p-6 rounded-lg shadow-md">
            <p class="text-lg text-gray-800 leading-relaxed text-center">{!! $post->content !!}</p>
        </div>
        
        <div class="bg-gray-50 p-6 rounded-lg shadow-sm mt-6">
            <h3 class="text-xl font-semibold text-gray-700 mb-4">Thông tin bổ sung</h3>
            <ul class="space-y-3 text-gray-600">
                <li><strong class="text-gray-800">Danh mục:</strong> 
                    @if($post->topic)
                        {{ $post->topic->name }}
                    @else
                        Không có danh mục
                    @endif
                </li>
                <li><strong class="text-gray-800">Số lượt xem:</strong> 123</li>
                <li><strong class="text-gray-800">Bình luận:</strong> 03 Bình luận</li>
            </ul>
        </div>
        <div class="mt-6 text-center mb-4">
            <a href="{{ url('/bai-viet') }}" class="text-blue-600 hover:text-blue-800 text-lg font-medium">Quay lại danh sách bài viết</a>
        </div>
    </div>
</x-layout-frontend>
