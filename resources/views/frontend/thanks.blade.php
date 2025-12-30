<x-layout-frontend>
    <section class="bg-gray-200 ml-5">
        <div class="breadcrumb flex items-center text-gray-600 text-sm  ">
            <span class="mr-4">Bạn đang ở đây:</span>
            <a href="{{url('/')}}" class="hover:text-orange-500"> Quay lại Trang chủ</a>
            <span class="mx-2">></span>
            <span class="font-semibold text-gray-800">Đơn hàng</span>
        </div>
    </section>

    <div class="max-w-2xl mx-auto text-center my-12">
        <h2 class="text-3xl font-bold mb-4">Thanh toán thành công!</h2>
        <p class="text-gray-600">Cảm ơn bạn đã đặt hàng. Chúng tôi sẽ xử lý đơn hàng của bạn sớm nhất.</p>
        <a href="{{ route('site.home') }}" class="mt-6 inline-block bg-orange-500 text-white py-2 px-4 rounded-md hover:bg-orange-600">
          Quay lại Trang chủ
        </a>
      </div>
</x-layout-frontend>