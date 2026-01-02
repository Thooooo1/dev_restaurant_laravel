@foreach ($posts as $item)
<div class="media post_item mb-4 flex  bg-gray-100 hover:bg-gray-200">
    <img src="{{ asset('/images/post/' . $item->image) }}" alt="{{$item->title}}"
        class="w-20 h-20 object-cover rounded-md">
    <div class="media-body ml-4">
        <a href="{{ route('site.blog.detail', ['slug'=>$item->slug])}}">
            <h3 class="text-gray-800 text-lg">{{$item->title}}
            </h3>
        </a>
        <p class="text-gray-600">{{$item->created_at}}</p>
    </div>
</div>
@endforeach
