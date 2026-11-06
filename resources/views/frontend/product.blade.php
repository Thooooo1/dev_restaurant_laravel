<x-layout-frontend>
    <main class="bg-stone-50/60 min-h-screen">
        <x-page-hero 
            title="Thực Đơn Món Ăn" 
            :breadcrumbs="[['name' => 'Thực đơn', 'url' => route('site.product.gridview')]]" 
        />

        <div class="py-8 sm:py-12">
            <x-list-filter-grid />
        </div>
    </main>
</x-layout-frontend>
