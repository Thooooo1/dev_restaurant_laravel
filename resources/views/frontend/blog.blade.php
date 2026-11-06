<x-layout-frontend>
    <main class="bg-stone-50/60 min-h-screen">  
        <!-- Page Hero Header with Breadcrumbs -->
        <x-page-hero 
            title="Cẩm nang ẩm thực & Chuyện quán" 
            subtitle="Chia sẻ kinh nghiệm ẩm thực, câu chuyện nguồn nguyên liệu sạch và gợi ý đặt tiệc tại CIVILIZE Phúc Thọ."
            :breadcrumbs="[['name' => 'Bài viết', 'url' => route('site.blog')]]" 
        />

        <!-- Khu vực Danh sách bài viết: Chuẩn container 1200px -->
        <section class="py-10 sm:py-14">
            <div class="max-w-[1200px] mx-auto px-4 sm:px-6 w-full">
                <x-list-blog />
            </div>
        </section>
    </main>
</x-layout-frontend>
