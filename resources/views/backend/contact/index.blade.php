<x-layout-backend>
    <!-- Page header -->
    <div class="mb-4">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-800">Hộp thư liên hệ</h1>
            </div>
            <div class="flex items-center gap-2">
                <a class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg text-sm font-medium transition-all shadow-lg shadow-red-500/25 hover:from-red-600 hover:to-red-700" href="{{ url('admin/contact/trash') }}">
                    <i class="fa-solid fa-trash"></i> Thùng rác
                </a>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <div class="table-responsive"><table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 text-center text-sm font-semibold text-slate-500 uppercase tracking-wider" style="width:30px;">#</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Họ tên</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Điện thoại</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Email</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Tiêu đề</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Nội dung</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold text-slate-500 uppercase tracking-wider" style="width:200px;">Chức năng</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold text-slate-500 uppercase tracking-wider" style="width:30px;">ID</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($contacts as $contact)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 text-center">
                                <input type="checkbox" id="checkId" value="1" name="checkId[]" class="rounded border-slate-300 text-orange-500 focus:ring-orange-500">
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-800 font-medium">{{ $contact->name }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ $contact->phone }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ $contact->email }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ $contact->title }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600 max-w-[200px] truncate">{{ $contact->content }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                @if ($contact->status == 1)
                                    <a href="{{ route('admin.contact.status', ['contact' => $contact->id]) }}" class="inline-flex items-center justify-center w-8 h-8 bg-green-50 text-green-600 rounded-lg text-sm transition-all hover:bg-green-100">
                                        <i class="fa-solid fa-toggle-on"></i>
                                    </a>
                                @else
                                    <a href="{{ route('admin.contact.status', ['contact' => $contact->id]) }}" class="inline-flex items-center justify-center w-8 h-8 bg-slate-100 text-slate-600 rounded-lg text-sm transition-all hover:bg-slate-200">
                                        <i class="fa-solid fa-toggle-off"></i>
                                    </a>
                                @endif
                                <a href="{{ route('admin.contact.show', ['contact' => $contact->id]) }}" class="inline-flex items-center justify-center w-8 h-8 bg-blue-50 text-blue-600 rounded-lg text-sm transition-all hover:bg-blue-100 ml-1">
                                    <i class="fa-regular fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.contact.edit', ['contact' => $contact->id]) }}" class="inline-flex items-center justify-center w-8 h-8 bg-orange-50 text-orange-600 rounded-lg text-sm transition-all hover:bg-orange-100 ml-1">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </a>
                                <a href="{{ route('admin.contact.delete', ['contact' => $contact->id]) }}" class="inline-flex items-center justify-center w-8 h-8 bg-red-50 text-red-600 rounded-lg text-sm transition-all hover:bg-red-100 ml-1">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center text-sm text-slate-600">
                                {{ $contact->id }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t border-slate-200 flex justify-center">
            {{-- {{ $contacts->links() }} --}}
        </div>
    </div>
</x-layout-backend>
