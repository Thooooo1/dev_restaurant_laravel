

<script>
$(document).ready(function () {
        $('.slick-slider').slick({
            infinite: true,
            slidesToShow: 5,
            slidesToScroll: 1,
            vertical: false,
            arrows: true,
            dots: true,
            prevArrow: '<button type="button" class="slick-prev p-2 text-white bg-gray-500 rounded-full"><i class="fas fa-chevron-left"></i></button>',
            nextArrow: '<button type="button" class="slick-next p-2 text-white bg-gray-500 rounded-full"><i class="fas fa-chevron-right"></i></button>',
            responsive: [
                {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 2,
                    }
                },
                {
                    breakpoint: 600,
                    settings: {
                        slidesToShow: 1,
                    }
                }
            ]
        })
    });
    document.querySelectorAll('.tab-btn').forEach(tabBtn  {
        tabBtn.addEventListener('click', function () {
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('bg-orange-500', 'text-white');
                btn.classList.add('bg-gray-100', 'hover:bg-gray-300');
            });

            tabBtn.classList.remove('bg-gray-100', 'hover:bg-gray-300');
            tabBtn.classList.add('bg-orange-500', 'text-white');

            document.querySelectorAll('.tab-content').forEach(tabContent => {
                tabContent.classList.add('hidden');
            });

            const tabContent = document.getElementById(tabBtn.dataset.tab);
            tabContent.classList.remove('hidden');
        })
    });
</script>