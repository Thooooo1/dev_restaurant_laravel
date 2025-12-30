<x-layout-frontend>
    <main>
        <section class="bg-gray-200 ml-5">
            <div class="breadcrumb flex items-center text-gray-600 text-sm  ">
                <span class="mr-4">Bạn đang ở đây:</span>
                <a href="{{url('/')}}" class="hover:text-orange-500"> Quay lại Trang chủ</a>
                <span class="mx-2">></span>               
                <span class="font-semibold text-gray-800">Hình ảnh</span>
            </div>
        </section>
       <section>
        <div class="py-16 px-4">
            <div class="max-w-7xl mx-auto">
                <h1 class="text-4xl font-bold text-center mb-12">Thư Viện Ảnh</h1>               
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                    @foreach ($images as $item)
                    <div class="group relative">
                        <img src="{{ asset('/images/library/' . $item->image) }}" alt="{{$item->title}}" class="w-full h-full object-cover rounded-lg shadow-lg transform transition duration-500 hover:scale-105">                        
                    </div>
                    @endforeach
                                     
                </div>
                <nav class="blog-pagination justify-center flex mt-8">
                    <ul class="pagination flex space-x-4">
                        @if ($images->onFirstPage())
                            <li><span class="px-4 py-2 text-gray-400 bg-gray-100 cursor-not-allowed rounded-md">Trang trước</span></li>
                        @else
                            <li><a href="{{ $images->previousPageUrl() }}" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-600">Trang trước</a></li>
                        @endif
            
                        @foreach ($images->links()->elements as $element)
                            @if (is_string($element))
                                <li><span class="px-4 py-2 text-gray-400 bg-gray-100 rounded-md">{{ $element }}</span></li>
                            @endif
            
                            @if (is_array($element))
                                @foreach ($element as $page => $url)
                                    @if ($page == $images->currentPage())
                                        <li><span class="px-4 py-2 bg-gray-800 text-white rounded-md">{{ $page }}</span></li>
                                    @else
                                        <li><a href="{{ $url }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-400">{{ $page }}</a></li>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach
            
                        @if ($images->hasMorePages())
                            <li><a href="{{ $images->nextPageUrl() }}" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-600">Trang sau</a></li>
                        @else
                            <li><span class="px-4 py-2 text-gray-400 bg-gray-100 cursor-not-allowed rounded-md">Trang sau</span></li>
                        @endif
                    </ul>
                </nav>
            </div>
        </div>
        
       </section>
    </main>
</x-layout-frontend>