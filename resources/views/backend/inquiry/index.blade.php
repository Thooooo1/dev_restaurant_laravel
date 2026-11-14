<x-layout-backend>
    <!-- Page Header (Ant Design Pro Style) -->
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-lg font-bold text-[rgba(0,0,0,0.85)] tracking-tight">Khách tư vấn trực tuyến</h1>
        </div>
    </div>
    
    <!-- Filter Bar (Shopify Polaris IndexFilters Style) -->
    <div class="ant-card p-3.5 mb-4 shadow-sm">
        <form method="GET" class="flex flex-wrap gap-3 items-center">
            <div class="flex items-center gap-2">
                <label for="status" class="text-xs font-semibold text-[rgba(0,0,0,0.65)] uppercase">Trạng thái:</label>
                <select name="status" id="status" class="polaris-select text-xs min-w-[180px]" onchange="this.form.submit()">
                    <option value="0" {{ $statusFilter == 0 ? 'selected' : '' }}>Tất cả trạng thái</option>
                    <option value="1" {{ $statusFilter == 1 ? 'selected' : '' }}>Chờ xử lý</option>
                    <option value="2" {{ $statusFilter == 2 ? 'selected' : '' }}>Đã liên hệ</option>
                    <option value="3" {{ $statusFilter == 3 ? 'selected' : '' }}>Hoàn thành</option>
                </select>
            </div>
            <button type="submit" class="ant-btn ant-btn-primary ant-btn-sm font-bold">
                <i class="fa-solid fa-filter text-xs"></i>
                <span>Lọc</span>
            </button>
            @if($statusFilter != 0)
                <a href="{{ url('admin/inquiries') }}" class="ant-btn ant-btn-sm text-gray-500 hover:text-black">
                    <span>Đặt lại</span>
                </a>
            @endif
        </form>
    </div>

    <!-- Data Table (Ant Design Pro / Shopify Polaris Style) -->
    <div class="ant-card shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="w-12 text-center" style="text-align: center !important;">#</th>
                        <th class="text-left" style="text-align: left !important;">Họ tên khách</th>
                        <th class="text-left" style="text-align: left !important;">Số điện thoại</th>
                        <th class="text-left" style="text-align: left !important;">Nội dung cần tư vấn</th>
                        <th class="text-left" style="text-align: left !important;">Thời gian gửi</th>
                        <th class="text-center w-28" style="text-align: center !important;">Trạng thái</th>
                        <th class="text-center w-20" style="text-align: center !important;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inquiries as $index => $inquiry)
                        <tr>
                            <td class="text-center font-mono text-xs text-[rgba(0,0,0,0.45)]" style="text-align: center !important;">
                                {{ $inquiries->firstItem() + $index }}
                            </td>
                            <td class="text-left" style="text-align: left !important;">
                                <div class="font-semibold text-xs text-[rgba(0,0,0,0.85)]">{{ $inquiry->name }}</div>
                            </td>
                            <td class="text-left font-mono text-xs text-[rgba(0,0,0,0.65)]" style="text-align: left !important;">
                                {{ $inquiry->phone }}
                            </td>
                            <td class="text-left text-xs text-[rgba(0,0,0,0.65)] max-w-[260px] truncate" style="text-align: left !important;" title="{{ $inquiry->message }}">
                                {{ $inquiry->message ?: '---' }}
                            </td>
                            <td class="text-left font-mono text-xs text-[rgba(0,0,0,0.65)] whitespace-nowrap" style="text-align: left !important;">
                                {{ date('H:i d/m/Y', strtotime($inquiry->created_at)) }}
                            </td>
                            <td class="text-center whitespace-nowrap text-xs text-[rgba(0,0,0,0.85)] font-medium" style="text-align: center !important;">
                                @switch($inquiry->status)
                                    @case(1) Chờ xử lý @break
                                    @case(2) Đã liên hệ @break
                                    @case(3) Hoàn thành @break
                                    @default Không xác định
                                @endswitch
                            </td>
                            <td class="text-center whitespace-nowrap" style="text-align: center !important;">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.inquiry.show', $inquiry->id) }}" class="w-7 h-7 rounded border border-gray-200 hover:bg-gray-100 text-gray-600 flex items-center justify-center text-xs transition-colors" title="Xem chi tiết & xử lý">
                                        <i class="fa-regular fa-eye"></i>
                                    </a>
                                    <form action="{{ route('admin.inquiry.destroy', $inquiry->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa yêu cầu tư vấn này?')" class="inline-block m-0 p-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-7 h-7 rounded border border-red-200 hover:bg-red-50 text-red-600 flex items-center justify-center text-xs transition-colors cursor-pointer" title="Xóa yêu cầu tư vấn">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-xs text-[rgba(0,0,0,0.45)]">
                                Không có yêu cầu tư vấn nào phù hợp
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
                
        <div class="px-4 py-3 border-t border-[#F0F0F0] flex justify-center">
            {{ $inquiries->appends(request()->query())->links() }}
        </div>
    </div>
</x-layout-backend>
