<x-layout-backend>
    <style>
        table thead th {
            font-size: 14px !important;
            font-weight: 600 !important;
            background: #78b4f1ff !important;
            padding-top: 0.75rem !important;
            padding-bottom: 0.75rem !important;
        }
    </style>
    <!-- Page header -->
    <div class="mb-4">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-800 flex items-center gap-3">
                    <span class="w-8 h-8 bg-gradient-to-br from-orange-400 to-orange-600 rounded-lg flex items-center justify-center text-white">
                        <i class="fa-solid fa-history text-sm"></i>
                    </span>
                    Lịch sử Order
                </h1>
                <p class="text-slate-500 mt-1 text-sm">Xem và quản lý lịch sử đơn hàng</p>
            </div>
        </div>
    </div>

    <!-- Stats card -->
    <div class="bg-gradient-to-r from-orange-400 to-orange-600 rounded-xl p-3 text-white mb-4 shadow-lg">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-white/20 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-coins text-base"></i>
            </div>
            <div>
                <p class="text-orange-100 text-xs font-medium">Tổng doanh thu (theo bộ lọc)</p>
                <p class="text-2xl font-bold">{{ number_format($totalRevenue, 0, ',', '.') }} VNĐ</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-3 mb-4">
        <form action="{{ route('admin.order-history.index') }}" method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[160px]">
                <label class="block text-sm font-medium text-slate-700 mb-1">Năm</label>
                <select name="year" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 transition-all outline-none bg-white text-sm" onchange="this.form.submit()">
                    @for($y = 2024; $y <= 2030; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="flex-1 min-w-[160px]">
                <label class="block text-sm font-medium text-slate-700 mb-1">Quý</label>
                <select name="quarter" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 transition-all outline-none bg-white text-sm" onchange="this.form.submit()">
                    <option value="">-- Tất cả --</option>
                    <option value="1" {{ $quarter == 1 ? 'selected' : '' }}>Quý 1</option>
                    <option value="2" {{ $quarter == 2 ? 'selected' : '' }}>Quý 2</option>
                    <option value="3" {{ $quarter == 3 ? 'selected' : '' }}>Quý 3</option>
                    <option value="4" {{ $quarter == 4 ? 'selected' : '' }}>Quý 4</option>
                </select>
            </div>

            <div class="flex-1 min-w-[160px]">
                <label class="block text-sm font-medium text-slate-700 mb-1">Tháng</label>
                <select name="month" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 transition-all outline-none bg-white text-sm" onchange="this.form.submit()">
                    <option value="">-- Tất cả --</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>Tháng {{ $m }}</option>
                    @endfor
                </select>
            </div>
        </form>
    </div>

    <!-- Orders table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <div class="table-responsive"><table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Mã HĐ</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Ngày tạo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Khách hàng</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">SĐT</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tổng tiền</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">PT Thanh toán</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($orders as $order)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="text-slate-800 font-semibold text-sm">#{{ $order->id }}</span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-slate-600 text-sm">
                            {{ $order->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-slate-800 font-medium text-sm">
                            {{ $order->user->fullname ?? $order->name ?? 'Khách lẻ' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-slate-600 text-sm">
                            {{ $order->user->phone ?? $order->phone }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-slate-800 font-semibold text-sm">
                            {{ number_format($order->total_price, 0, ',', '.') }} VNĐ
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($order->payment_method == 1)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-medium">
                                    <i class="fa-solid fa-money-bill-wave"></i>
                                    Tiền mặt
                                </span>
                            @elseif($order->payment_method == 2)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-medium">
                                    <i class="fa-solid fa-university"></i>
                                    Chuyển khoản
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 text-amber-600 rounded-full text-xs font-medium">
                                    <i class="fa-solid fa-question-circle"></i>
                                    Khác
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            <button class="btn-view-detail inline-flex items-center justify-center w-10 h-10 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg transition-colors" data-id="{{ $order->id }}" onclick="openOrderDetail({{ $order->id }})">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($orders->count() == 0)
        <div class="text-center py-8">
            <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-inbox text-slate-400 text-xl"></i>
            </div>
            <p class="text-slate-500 font-medium text-sm">Không có đơn hàng nào</p>
        </div>
        @endif
        
        <div class="px-4 py-3 border-t border-slate-200 flex justify-center">
            {{ $orders->links() }}
        </div>
    </div>

    <!-- Modal Chi tiết đơn hàng -->
    <div class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4" id="orderDetailModal">
        <div class="bg-white rounded-xl shadow-xl max-w-3xl w-full max-h-[90vh] overflow-auto">
            <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                <h3 class="text-base font-semibold text-slate-800">Chi tiết đơn hàng</h3>
                <button onclick="closeModal('orderDetailModal')" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 transition-colors">
                    <i class="fa-solid fa-times text-slate-400 text-sm"></i>
                </button>
            </div>
            
            <div class="p-4" id="modalContent">
                <div class="text-center py-6">
                    <i class="fa-solid fa-spinner fa-spin text-slate-400 text-xl mb-2"></i>
                    <p class="text-slate-500 text-sm">Đang tải...</p>
                </div>
            </div>
            
            <div class="px-4 py-3 border-t border-slate-200 flex justify-end">
                <button onclick="closeModal('orderDetailModal')" class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition-colors text-sm">
                    Đóng
                </button>
            </div>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        
        function openOrderDetail(orderId) {
            $('#modalContent').html(`
                <div class="text-center py-6">
                    <i class="fa-solid fa-spinner fa-spin text-slate-400 text-xl mb-2"></i>
                    <p class="text-slate-500 text-sm">Đang tải...</p>
                </div>
            `);
            openModal('orderDetailModal');
            
            $.ajax({
                url: '/admin/order-history/' + orderId,
                type: 'GET',
                success: function(response) {
                    $('#modalContent').html(response.html);
                },
                error: function() {
                    $('#modalContent').html(`
                        <div class="text-center py-6">
                            <i class="fa-solid fa-exclamation-circle text-red-400 text-xl mb-2"></i>
                            <p class="text-red-500 text-sm">Không thể tải thông tin đơn hàng.</p>
                        </div>
                    `);
                }
            });
        }
    </script>
</x-layout-backend>
