@if (count($menus) > 0)
    <ul class="space-y-2.5">
        @foreach ($menus as $item)             
            <li>
                <a href="{{ url($item->link) }}" class="text-stone-300 hover:text-amber-400 transition-colors duration-200 text-xs sm:text-sm flex items-center gap-2 group">
                    <i class="fas fa-chevron-right text-[10px] text-amber-500/60 group-hover:translate-x-1 transition-transform"></i>
                    <span>{{ $item->name }}</span>
                </a>
            </li>               
        @endforeach
    </ul>
@else
    <ul class="space-y-2.5">
        <li>
            <a href="{{ url('/dieu-khoan-su-dung') }}" class="text-stone-300 hover:text-amber-400 transition-colors duration-200 text-xs sm:text-sm flex items-center gap-2 group">
                <i class="fas fa-chevron-right text-[10px] text-amber-500/60 group-hover:translate-x-1 transition-transform"></i>
                <span>Điều khoản sử dụng</span>
            </a>
        </li>
        <li>
            <a href="{{ url('/chinh-sach-bao-mat') }}" class="text-stone-300 hover:text-amber-400 transition-colors duration-200 text-xs sm:text-sm flex items-center gap-2 group">
                <i class="fas fa-chevron-right text-[10px] text-amber-500/60 group-hover:translate-x-1 transition-transform"></i>
                <span>Chính sách bảo mật</span>
            </a>
        </li>
        <li>
            <a href="{{ url('/chinh-sach-an-toan-thuc-pham') }}" class="text-stone-300 hover:text-amber-400 transition-colors duration-200 text-xs sm:text-sm flex items-center gap-2 group">
                <i class="fas fa-chevron-right text-[10px] text-amber-500/60 group-hover:translate-x-1 transition-transform"></i>
                <span>An toàn thực phẩm</span>
            </a>
        </li>
        <li>
            <a href="{{ url('/lien-he') }}" class="text-stone-300 hover:text-amber-400 transition-colors duration-200 text-xs sm:text-sm flex items-center gap-2 group">
                <i class="fas fa-chevron-right text-[10px] text-amber-500/60 group-hover:translate-x-1 transition-transform"></i>
                <span>Liên hệ hỗ trợ</span>
            </a>
        </li>
    </ul>
@endif


