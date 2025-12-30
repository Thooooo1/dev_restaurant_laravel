@if ($posts->isEmpty())
    <div class="text-center py-8">
        <p class="text-gray-600 text-lg">Không có bài viết nào để hiển thị.</p>
    </div>
@else
    @foreach ($posts as $post)
    <article class="blog_item mb-8">
        <div class="blog_item_img relative">
            <img class="rounded-0 w-full" src="{{ asset('images/post/' . $post->image) }}" alt="{{ $post->title }}">
            <p class="text-gray-600 text-lg mb-4">Ngày đăng: {{ \Carbon\Carbon::parse($post->created_at)->format('d-m-Y') }}</p>
            <a href="{{ route('site.blog.detail', ['slug'=>$post->slug]) }}" class="blog_item_date absolute top-0 left-0 bg-gray-800 text-white p-4">
                <h3 class="text-2xl">{{ $post->created_at->format('d') }}</h3>
                <p>{{ $post->created_at->format('M') }}</p>
            </a>
        </div>
        <div class="blog_details mt-4">
            <a class="d-inline-block" href="{{ route('site.blog.detail', ['slug'=>$post->slug]) }}">
                <h2 class="text-gray-800 text-xl font-bold">{{ $post->title }}</h2>
            </a>
            <p class="mt-2">{{ $post->description }}</p>
            <ul class="blog-info-link flex mt-4 space-x-4">
                <li><a href="{{ route('site.blog', ['topic' => $post->id]) }}" class="text-blue-600"><i class="fa fa-user"></i> {{ $post->topic->name }}</a></li>
                <li><a href="#" class="text-blue-600"><i class="fa fa-comments"></i> 30 Bình luận</a></li>
            </ul>
        </div>
    </article>
    @endforeach

    <nav class="blog-pagination justify-center flex mt-8">
        <ul class="pagination flex space-x-4">
            @if ($posts->onFirstPage())
                <li><span class="px-4 py-2 text-gray-400 bg-gray-100 cursor-not-allowed rounded-md">Trang trước</span></li>
            @else
                <li><a href="{{ $posts->previousPageUrl() }}" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-600">Trang trước</a></li>
            @endif

            @foreach ($posts->links()->elements as $element)
                @if (is_string($element))
                    <li><span class="px-4 py-2 text-gray-400 bg-gray-100 rounded-md">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $posts->currentPage())
                            <li><span class="px-4 py-2 bg-gray-800 text-white rounded-md">{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $url }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-400">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($posts->hasMorePages())
                <li><a href="{{ $posts->nextPageUrl() }}" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-600">Trang sau</a></li>
            @else
                <li><span class="px-4 py-2 text-gray-400 bg-gray-100 cursor-not-allowed rounded-md">Trang sau</span></li>
            @endif
        </ul>
    </nav>
@endif
