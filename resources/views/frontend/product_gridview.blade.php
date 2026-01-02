<x-layout-frontend>
    <div class="bg-gray-100 ">
        <!-- Breadcrumb -->
        <div class="container mx-auto px-4 py-3">
            <div class="breadcrumb flex items-center text-gray-600 text-sm  mt-4">
                <span class="mr-4">Bạn đang ở đây:</span>
                <a href="{{ url('/') }}" class="hover:text-orange-500">Trang chủ</a>
                <span class="mx-2">></span>
                <span class="font-semibold text-gray-800">Thực đơn</span>
            </div>
        </div>
      
        <!-- Main Content -->
       <x-list-filter-grid/>
      </div>
</x-layout-frontend>