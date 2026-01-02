
<section class="blogs-area py-24">
    <div class="container mx-auto">
        <div class="flex justify-center mb-5">
            <div class="lg:w-1/2 text-center">
                <div class="section-title mb-16">
                    <span class="text-orange-500 font-semibold uppercase text-sm tracking-wide">Về Tin
                        tức</span>
                    <h2 class="text-3xl font-bold text-gray-800 mt-2">Tin tức mới nhất</h2>
                </div>
            </div>
        </div>

        <div class="relative">
            <div class="overflow-x-auto">
                <div class="flex space-x-10" id="blog-container">
                    @foreach ($posts as $item)
                    <div class="single-blogs flex-shrink-0 w-80 group relative rounded-lg overflow-hidden transition-all transform hover:scale-105 hover:shadow-lg">
                        <div class="blog-img mb-6">
                            <img src="{{ asset('/images/post/' . $item->image) }}" alt="{{$item->title}}"
                                class="w-80 h-80 object-cover rounded-lg transition-all duration-300 group-hover:opacity-80">
                        </div>
                        
                        <div class="blog-cap p-6 bg-white rounded-lg">
                            <span class="text-orange-500 block mb-2">
                                {{ \Carbon\Carbon::parse($item->created_at)->format('H:i, d-m-Y') }}
                            </span>
                            
                            <h4 class="text-xl font-semibold text-gray-800 group-hover:text-orange-500">
                                <a href="{{ route('site.blog.detail', ['slug'=>$item->slug])}}">{{$item->title}}</a>
                            </h4>
                        </div>
                    </div>
                    @endforeach
                  
                    

                </div>
            </div>
        </div>
    </div>
</section>