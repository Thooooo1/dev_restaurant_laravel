<aside class="single_sidebar_widget tag_cloud_widget mb-8">
    <h4 class="widget_title text-gray-800 text-xl mb-4 font-bold">Tìm kiếm gần đây</h4>
    <ul class="list space-x-4 flex flex-wrap">
        @foreach ($keywords as $item)
            <li><a href="#" class="text-blue-600 border bg-gray-100 hover:bg-gray-200 ">{{$item->title}}</a></li>
        @endforeach
       
    </ul>
</aside>