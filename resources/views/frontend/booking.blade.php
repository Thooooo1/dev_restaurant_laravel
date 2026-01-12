<x-layout-frontend>
    <section class="section_booktable">
        <div class="booking-area bg-cover bg-center pt-32 pb-32"
            style="background-image: url('../images/gallery/section_bg04.png');">
            <div class="container mx-auto">
                {{-- <div class="row justify-center">
                    <div class="xl:w-10/12 lg:w-9/12 md:w-10/12">
                        <div class="text-center mb-16 ml-10">
                            <span class="text-orange-500 font-semibold uppercase text-sm tracking-wide">Đặt
                                bàn</span>
                            <h2 class="text-3xl font-bold text-gray-800 mt-2">Lên lịch đặt bàn</h2>
                        </div>
                    </div>
                </div> --}}

                <div class="row justify-center">
                    <div class="">
                        <div class="text-center mb-16">
                            <span class="text-orange-500 font-semibold uppercase text-sm tracking-wide">Đặt
                                bàn</span>
                            <h2 class="text-3xl font-bold text-gray-800 mt-2">Lên lịch đặt bàn</h2>
                        </div>
                    </div>
                </div>

                <div class="row justify-center">
                    <div class="col-12">
                        <form action="{{ route('site.booking.store') }}" method="POST" class="max-w-3xl mx-auto bg-white p-6 rounded-lg shadow-md">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="w-full mb-4">
                                    <input type="text" name="name" placeholder="Tên của bạn" required class="w-full px-4 py-2 border rounded-md" />
                                </div>
                                <div class="w-full mb-4">
                                    <input type="email" name="email" placeholder="Email của bạn" required class="w-full px-4 py-2 border rounded-md" />
                                </div>
                                <div class="w-full mb-4">
                                    <input type="text" name="phone" placeholder="Số điện thoại" required class="w-full px-4 py-2 border rounded-md" />
                                </div>
                                <div class="w-full mb-4">
                                    <input type="number" name="number_of_people" placeholder="Số người" required class="w-full px-4 py-2 border rounded-md" />
                                </div>
                                <div class="w-full mb-4">
                                    <input type="date" name="booking_date" required class="w-full px-4 py-2 border rounded-md" />
                                </div>
                                <div class="w-full mb-4">
                                    <input type="time" name="booking_time" required class="w-full px-4 py-2 border rounded-md" />
                                </div>
                            </div>
                            <div class="w-full mb-4">
                                <textarea name="note" placeholder="Ghi chú" rows="3" class="w-full px-4 py-2 border rounded-md"></textarea>
                            </div>
                            <div class="w-full">
                                <button type="submit" class="w-full px-6 py-3 bg-orange-500 text-white rounded-md">Đặt bàn ngay</button>
                            </div>
                            <div class="text-center mt-4">
                                <p class="text-gray-700 text-sm">
                                    Khách đặt tiệc hội nghị, liên hoan vui lòng gọi trực tiếp:
                                    <a href="tel:981487674" class="text-orange-500 font-semibold">981487674</a>
                                </p>
                            </div>
                        </form>
                        
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layout-frontend>