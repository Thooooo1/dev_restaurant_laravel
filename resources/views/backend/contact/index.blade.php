<x-layout-backend>
    <!-- Page Header (Ant Design Pro Style) -->
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-lg font-bold text-[rgba(0,0,0,0.85)] tracking-tight">Hộp thư liên hệ</h1>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ url('admin/contact/trash') }}" class="ant-btn ant-btn-sm text-gray-500 hover:text-red-600 font-medium bg-white border border-gray-200">
                <span>Thùng rác</span>
            </a>
        </div>
    </div>

    <!-- Data Table (Ant Design Pro / Shopify Polaris Style) -->
    <div class="ant-card shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="w-12 text-center" style="text-align: center !important;">ID</th>
                        <th class="text-left" style="text-align: left !important;">Họ tên khách</th>
                        <th class="text-left" style="text-align: left !important;">Điện thoại</th>
                        <th class="text-left" style="text-align: left !important;">Email</th>
                        <th class="text-left" style="text-align: left !important;">Tiêu đề</th>
                        <th class="text-left" style="text-align: left !important;">Nội dung tin nhắn</th>
                        <th class="text-center w-24" style="text-align: center !important;">Trạng thái</th>
                        <th class="text-center w-28" style="text-align: center !important;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($contacts as $contact)
                        <tr>
                            <td class="text-center font-mono text-xs text-[rgba(0,0,0,0.45)]" style="text-align: center !important;">
                                #{{ $contact->id }}
                            </td>
                            <td class="text-left" style="text-align: left !important;">
                                <div class="font-semibold text-xs text-[rgba(0,0,0,0.85)]">{{ $contact->name }}</div>
                            </td>
                            <td class="text-left font-mono text-xs text-[rgba(0,0,0,0.65)]" style="text-align: left !important;">
                                {{ $contact->phone }}
                            </td>
                            <td class="text-left text-xs text-[rgba(0,0,0,0.45)]" style="text-align: left !important;">
                                {{ $contact->email ?: '---' }}
                            </td>
                            <td class="text-left text-xs text-[rgba(0,0,0,0.85)] font-medium max-w-[150px] truncate" style="text-align: left !important;" title="{{ $contact->title }}">
                                {{ $contact->title ?: 'Không tiêu đề' }}
                            </td>
                            <td class="text-left text-xs text-[rgba(0,0,0,0.65)] max-w-[220px] truncate" style="text-align: left !important;" title="{{ $contact->content }}">
                                {{ $contact->content ?: '---' }}
                            </td>
                            <td class="text-center whitespace-nowrap" style="text-align: center !important;">
                                @if ($contact->status == 1)
                                    <a href="{{ route('admin.contact.status', ['contact' => $contact->id]) }}" class="text-gray-700 hover:text-black text-xs font-medium" title="Đã duyệt - bấm để chuyển trạng thái">
                                        <i class="fa-solid fa-toggle-on text-base text-gray-800"></i>
                                    </a>
                                @else
                                    <a href="{{ route('admin.contact.status', ['contact' => $contact->id]) }}" class="text-gray-400 hover:text-gray-600 text-xs font-medium" title="Chờ xử lý - bấm để chuyển trạng thái">
                                        <i class="fa-solid fa-toggle-off text-base text-gray-400"></i>
                                    </a>
                                @endif
                            </td>
                            <td class="text-center whitespace-nowrap" style="text-align: center !important;">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.contact.show', ['contact' => $contact->id]) }}" class="w-7 h-7 rounded border border-gray-200 hover:bg-gray-100 text-gray-600 flex items-center justify-center text-xs transition-colors" title="Xem chi tiết">
                                        <i class="fa-regular fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.contact.edit', ['contact' => $contact->id]) }}" class="w-7 h-7 rounded border border-gray-200 hover:bg-gray-100 text-gray-600 flex items-center justify-center text-xs transition-colors" title="Chỉnh sửa">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </a>
                                    <a href="{{ route('admin.contact.delete', ['contact' => $contact->id]) }}" class="w-7 h-7 rounded border border-gray-200 hover:bg-red-50 text-gray-600 hover:text-red-600 flex items-center justify-center text-xs transition-colors" title="Xóa vào thùng rác">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-xs text-[rgba(0,0,0,0.45)]">
                                Chưa có thư liên hệ nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($contacts, 'links'))
            <div class="px-4 py-3 border-t border-[#F0F0F0] flex justify-center">
                {{ $contacts->links() }}
            </div>
        @endif
    </div>
</x-layout-backend>
