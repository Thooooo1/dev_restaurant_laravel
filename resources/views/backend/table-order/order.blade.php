<x-layout-backend>

    <!-- Top Action Bar (Toast POS / Ant Design Pro Style) -->
    <div class="bg-white border border-[#F0F0F0] rounded p-4 mb-4 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <!-- Left: Table & Floor Context -->
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-lg font-bold text-[rgba(0,0,0,0.85)] tracking-tight">{{ $table->name }}</h1>
                    <span class="polaris-badge polaris-badge-default">{{ $table->floor->name ?? 'Tầng 1' }}</span>
                    @if($order->has_new_items)
                        <span class="polaris-badge polaris-badge-error animate-pulse">Có món mới</span>
                    @else
                        <span class="polaris-badge polaris-badge-processing">Đang phục vụ</span>
                    @endif
                </div>
                <p class="text-xs text-[rgba(0,0,0,0.45)] mt-0.5">
                    Mã đơn: <span class="font-mono font-medium text-[rgba(0,0,0,0.65)]">#{{ $order->id }}</span> • Giờ vào: {{ $order->created_at ? $order->created_at->format('H:i') : '--:--' }}
                </p>
            </div>

            <!-- Right: Real-time Total & Action Buttons -->
            <div class="flex flex-wrap items-center gap-3 justify-between md:justify-end">
                <div class="flex items-center gap-2 mr-2">
                    <span class="text-xs text-[rgba(0,0,0,0.45)] uppercase font-semibold">Tổng tiền:</span>
                    <span class="text-xl font-bold font-mono text-[#F47716]" id="totalAmountDisplay">0 ₫</span>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('admin.table-order.index') }}" class="ant-btn ant-btn-sm" id="backBtn">
                        <i class="fa-solid fa-arrow-left text-xs"></i>
                        <span>Bàn khác</span>
                    </a>
                    <button type="button" class="ant-btn ant-btn-sm" id="printKitchenBtn" onclick="printKitchen()">
                        <span>Báo bếp</span>
                    </button>
                    <button type="button" class="ant-btn ant-btn-sm {{ $hasAnyItems ? '' : 'opacity-50 pointer-events-none' }}" id="printBillBtn" onclick="printTableBill()">
                        <i class="fa-solid fa-print text-xs"></i>
                        <span>In tạm tính</span>
                    </button>
                    <a href="{{ route('admin.table-order.payment', $table->id) }}" class="ant-btn ant-btn-primary ant-btn-sm {{ $hasAnyItems ? '' : 'opacity-50 pointer-events-none' }}" id="payBtn">
                        <i class="fa-solid fa-money-bill-wave text-xs"></i>
                        <span>Thanh toán</span>
                    </a>
                    @if(!$hasAnyItems)
                        <button type="button" class="ant-btn ant-btn-sm text-amber-600" onclick="showChangeTableModal()">
                            <i class="fa-solid fa-exchange-alt text-xs"></i>
                            <span>Đổi bàn</span>
                        </button>
                        <button type="button" class="ant-btn ant-btn-danger ant-btn-sm" onclick="confirmCancelTable()">
                            <i class="fa-solid fa-xmark text-xs"></i>
                            <span>Hủy bàn</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($order->has_new_items)
        <div class="mb-4 bg-[#FFFBE6] border border-[#FFE58F] text-[#D46B08] px-4 py-2.5 rounded text-xs flex items-center justify-between">
            <span class="flex items-center gap-2">
                <i class="fa-solid fa-bell text-amber-500 animate-bounce"></i>
                <strong>Thông báo bếp:</strong> Khách hàng vừa gửi thêm món mới từ thực đơn QR. Vui lòng kiểm tra và báo bếp!
            </span>
            <button type="button" class="text-xs text-amber-700 hover:underline font-medium" onclick="$(this).parent().fadeOut()">Đã xem</button>
        </div>
    @endif

    <!-- Main POS Grid: 2 Columns (Toast POS Architecture) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
        <!-- LEFT COLUMN: Vé gọi món của Bàn (lg:col-span-5) -->
        <div class="lg:col-span-5 flex flex-col gap-3">
            <div class="ant-card p-4 flex flex-col h-full shadow-sm">
                <!-- Customer Info & Notes -->
                <div class="pb-3 border-b border-[#F0F0F0] flex items-center justify-between text-xs">
                    @php
                        $dispCustName = $order->customer_name;
                        $dispCustPhone = $order->customer_phone;
                        $hasCustomName = !empty($dispCustName) && $dispCustName !== 'Khách tại bàn' && !str_starts_with(mb_strtolower($dispCustName), 'bàn');
                        $hasCustomPhone = !empty($dispCustPhone) && $dispCustPhone !== '---' && $dispCustPhone !== '—' && $dispCustPhone !== '0900000000';
                    @endphp
                    <div>
                        <span class="text-[rgba(0,0,0,0.45)]">Khách:</span>
                        <strong class="text-[rgba(0,0,0,0.85)] ml-1">{{ $hasCustomName ? $dispCustName : 'Khách tại bàn' }}</strong>
                        @if($hasCustomPhone)
                            <span class="text-[rgba(0,0,0,0.45)] ml-1 font-mono">({{ $dispCustPhone }})</span>
                        @endif
                    </div>
                    <span class="polaris-badge polaris-badge-default">{{ $order->number_of_guests ?? 2 }} khách</span>
                </div>

                <!-- Order Note Input -->
                <div class="mt-2.5 mb-3">
                    <input type="text" id="orderNote" class="polaris-input text-xs" placeholder="Ghi chú món ăn cho bếp (VD: ít cay, không hành...)" value="{{ $order->note }}">
                </div>

                <!-- Existing Items Section (Accordion or Itemized List) -->
                @php
                    $pendingItems = $orderDetails->filter(function($item) { return $item->status == 'pending'; });
                    $confirmedItems = $orderDetails->filter(function($item) { return $item->status != 'pending'; });
                @endphp

                <!-- 1. Món chờ xác nhận từ khách QR -->
                @if($pendingItems->count() > 0)
                    <div class="mb-3 border border-amber-200 bg-amber-50/50 rounded overflow-hidden">
                        <div class="px-3 py-1.5 bg-amber-100 text-amber-900 font-semibold text-xs flex items-center justify-between">
                            <span>Món mới khách gọi thêm ({{ $pendingItems->count() }})</span>
                            <span class="polaris-badge polaris-badge-warning text-[10px]">CHỜ BÁO BẾP</span>
                        </div>
                        <div class="p-2 space-y-1.5">
                            @foreach($pendingItems as $it)
                                @php
                                    $price = $it->price ?? ($it->product->price_sale ?? 0);
                                    $amount = $price * ($it->qty ?? 0);
                                @endphp
                                <div class="flex items-center justify-between py-1.5 border-b border-amber-100/80 last:border-0 text-xs" id="pending-item-{{ $it->id }}" data-price="{{ $price }}">
                                    <div>
                                        <div class="font-semibold text-slate-800">{{ $it->product->name ?? 'Món ăn' }}</div>
                                        <div class="text-[11px] text-slate-500">{{ number_format($price, 0, ',', '.') }} ₫ × {{ $it->qty }}</div>
                                    </div>
                                    <div class="font-mono font-bold text-slate-800" id="pending-amount-{{ $it->id }}">{{ number_format($amount, 0, ',', '.') }} ₫</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- 2. Món đã gọi & đã phục vụ -->
                @if($confirmedItems->count() > 0)
                    <div class="mb-3 border border-[#F0F0F0] rounded overflow-hidden bg-white">
                        <div class="px-3 py-1.5 bg-[#FAFAFA] border-b border-[#F0F0F0] text-xs font-semibold text-[rgba(0,0,0,0.65)] flex items-center justify-between">
                            <span>Món ăn đã gọi ({{ $confirmedItems->count() }})</span>
                            <span class="text-[11px] text-[rgba(0,0,0,0.45)]">Lượt {{ $order->order_turn ?? 1 }}</span>
                        </div>
                        <div class="divide-y divide-[#F0F0F0] max-h-64 overflow-y-auto px-3">
                            @foreach($confirmedItems as $it)
                                @php
                                    $price = $it->price ?? ($it->product->price_sale ?? 0);
                                    $amount = $price * ($it->qty ?? 0);
                                    $statusText = '';
                                    $badgeClass = 'polaris-badge-processing';
                                    switch($it->status) {
                                        case 'cooking':
                                            $statusText = 'Đang nấu';
                                            $badgeClass = 'polaris-badge-warning';
                                            break;
                                        case 'served':
                                            $statusText = 'Đã lên món';
                                            $badgeClass = 'polaris-badge-success';
                                            break;
                                        default:
                                            $statusText = 'Đã xác nhận';
                                            $badgeClass = 'polaris-badge-processing';
                                    }
                                @endphp
                                <div class="py-2 flex items-center justify-between text-xs" id="history-item-{{ $it->id }}" data-price="{{ $price }}">
                                    <div class="flex-1 pr-2">
                                        <div class="font-medium text-[rgba(0,0,0,0.85)]">{{ $it->product->name ?? 'Món' }}</div>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-[11px] text-[rgba(0,0,0,0.45)] font-mono">{{ number_format($price, 0, ',', '.') }} ₫ × {{ $it->qty }}</span>
                                            <span class="polaris-badge {{ $badgeClass }} text-[10px] py-0 px-1.5">{{ $statusText }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="font-mono font-semibold text-[rgba(0,0,0,0.85)]" id="history-amount-{{ $it->id }}">
                                            {{ number_format($amount, 0, ',', '.') }} ₫
                                        </div>
                                        <div class="flex items-center border border-[#D9D9D9] rounded overflow-hidden">
                                            <button class="w-5 h-5 bg-white hover:bg-gray-100 flex items-center justify-center text-xs text-gray-600" onclick="decrementHistory({{ $it->id }})">-</button>
                                            <span class="w-6 h-5 bg-[#FAFAFA] flex items-center justify-center text-[11px] font-mono border-x border-[#D9D9D9]" id="history-qty-{{ $it->id }}">{{ $it->qty }}</span>
                                            <button class="w-5 h-5 bg-gray-50 text-gray-300 flex items-center justify-center text-xs" disabled>+</button>
                                        </div>
                                        @if($it->status != 'served')
                                            <button class="w-6 h-6 rounded bg-emerald-50 text-emerald-600 hover:bg-emerald-100 flex items-center justify-center text-xs transition-colors" onclick="markItemAsServed({{ $it->id }})" title="Đã lên món">
                                                <i class="fa-solid fa-check text-[10px]"></i>
                                            </button>
                                        @endif
                                        <button class="text-gray-400 hover:text-red-500 transition-colors p-1" onclick="deleteHistory({{ $it->id }})" title="Xóa món">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Dynamic Items (Being added right now, unsaved) -->
                <div class="flex-1 overflow-y-auto min-h-[160px] border border-dashed border-[#D9D9D9] rounded p-2.5 bg-[#FAFAFA]">
                    <div class="text-[11px] font-semibold text-[rgba(0,0,0,0.45)] uppercase tracking-wider mb-2">Món mới đang chọn</div>
                    <div id="orderItemsContent" class="space-y-1.5">
                        <!-- Items rendered by displayOrderItems() -->
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: Thực đơn chọn món (Toast POS Menu Picker) (lg:col-span-7) -->
        <div class="lg:col-span-7 flex flex-col gap-3">
            <div class="ant-card p-4 shadow-sm flex flex-col h-full">
                <!-- Categories Pills (Toast POS Style) -->
                <div class="mb-3 pb-3 border-b border-[#F0F0F0]">
                    <div class="flex items-center gap-2 overflow-x-auto pb-1" id="categoryMenu">
                        @foreach($categories as $category)
                            <button type="button" 
                                    class="category-item px-3 py-1.5 rounded text-xs font-medium whitespace-nowrap transition-colors {{ $loop->first ? 'active bg-[#F47716] text-white border-transparent' : 'bg-white text-[rgba(0,0,0,0.65)] border border-[#D9D9D9] hover:border-[#F47716]' }}" 
                                    data-id="{{ $category->id }}">
                                {{ $category->name }}
                            </button>
                        @endforeach
                    </div>
                    <input type="hidden" id="categoryFilter" value="{{ $categories->first()->id ?? '' }}">
                </div>

                <!-- Products Grid -->
                <div id="productsList" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 overflow-y-auto" style="max-height: 600px;">
                    <!-- Products will be loaded here via Ajax -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Đổi bàn (Ant Design Pro Dialog Style) -->
    <div class="modal fade" id="changeTableModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-xl rounded-lg overflow-hidden">
                <div class="px-5 py-4 border-b border-[#F0F0F0] flex items-center justify-between bg-[#FAFAFA]">
                    <h5 class="font-semibold text-sm text-[rgba(0,0,0,0.85)]">Chuyển sang bàn khác</h5>
                    <button type="button" class="text-gray-400 hover:text-gray-600 text-sm" data-dismiss="modal">&times;</button>
                </div>
                <div class="p-5 max-h-96 overflow-y-auto">
                    <div class="grid grid-cols-3 gap-3">
                        @foreach($availableTables as $tbl)
                            <div class="change-table-item p-3 border border-[#D9D9D9] rounded text-center cursor-pointer hover:border-[#F47716] hover:bg-orange-50/30 transition-all" onclick="selectTable({{ $tbl->id }})" data-table-id="{{ $tbl->id }}">
                                <i class="fa-solid fa-chair text-gray-400 mb-1 text-lg"></i>
                                <div class="font-semibold text-xs text-slate-800">{{ $tbl->name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $tbl->floor->name ?? '' }}</div>
                            </div>
                        @endforeach
                        @if(empty($availableTables) || count($availableTables) == 0)
                            <div class="col-span-3 text-center py-6 text-xs text-slate-400">Không có bàn trống nào khác</div>
                        @endif
                    </div>
                </div>
                <div class="px-5 py-3 border-t border-[#F0F0F0] bg-[#FAFAFA] flex items-center justify-end gap-2">
                    <button type="button" class="ant-btn ant-btn-sm" data-dismiss="modal">Hủy</button>
                    <button type="button" class="ant-btn ant-btn-primary ant-btn-sm" id="confirmChangeTableBtn" onclick="confirmChangeTable()" disabled>Xác nhận chuyển</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .category-item.active {
            background-color: #F47716 !important;
            color: #FFFFFF !important;
            border-color: #F47716 !important;
        }
        .product-card {
            background: #FFFFFF;
            border: 1px solid #E5E7EB;
            border-radius: 4px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.645, 0.045, 0.355, 1);
            display: flex;
            flex-direction: column;
        }
        .product-card:hover {
            border-color: #F47716;
            box-shadow: 0 2px 8px rgba(244, 119, 22, 0.12);
            transform: translateY(-1px);
        }
        .product-img {
            width: 100%;
            height: 96px;
            object-fit: cover;
            background-color: #F8FAFC;
        }
        .product-name {
            font-size: 12px;
            font-weight: 600;
            color: rgba(0, 0, 0, 0.85);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.35;
        }
        .order-stepper-btn {
            width: 22px;
            height: 22px;
            background: #FFFFFF;
            border: 1px solid #D9D9D9;
            border-radius: 3px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: bold;
            color: rgba(0, 0, 0, 0.65);
            cursor: pointer;
            transition: all 0.15s;
        }
        .order-stepper-btn:hover {
            border-color: #F47716;
            color: #F47716;
        }
    </style>

    <script>
        let currentTableId = {{ $table->id }};
        let currentOrderId = {{ $order->id }};
        let orderTurn = {{ $order->order_turn ?? 1 }};
        let currentOrderDetails = [];
        let existingOrderDetails = @json($orderDetails); // Load existing items
        let originalOrderDetails = [];
        let allProducts = [];
        let isOrderSaved = true;
        let hasExistingItems = {{ $hasAnyItems ? 'true' : 'false' }};
        let selectedNewTableId = null;

        $(document).ready(function() {
            // Initial render
            displayOrderItems();
            loadProducts();

            // Category click
            $(document).on('click', '.category-item', function() {
                $('.category-item').removeClass('active');
                $(this).addClass('active');
                const categoryId = $(this).data('id');
                $('#categoryFilter').val(categoryId);
                loadProducts();
            });

            // Monitor Note change
            $('#orderNote').on('input', function() {
                isOrderSaved = false;
            });

            // Warn on leave if unsaved
            window.onbeforeunload = function() {
                if (!isOrderSaved && currentOrderDetails.length > 0) {
                    return "Bạn có thay đổi chưa lưu. Bạn có chắc chắn muốn rời đi?";
                }
            };
        });

        // Load products
        function loadProducts() {
            const categoryId = $('#categoryFilter').val();
            
            $.ajax({
                url: '{{ route("admin.table-order.getProducts") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    category_id: categoryId
                },
                success: function(response) {
                    if (response.success) {
                        let html = '';
                        response.products.forEach(function(product) {
                            // Update cache
                            const existingIndex = allProducts.findIndex(p => p.id === product.id);
                            if (existingIndex >= 0) {
                                allProducts[existingIndex] = product;
                            } else {
                                allProducts.push(product);
                            }

                            let imagePath = product.image ? '{{ asset("/images/product") }}/' + product.image : '{{ asset("/images/default-product.png") }}';
                            html += `
                                <div class="product-card" onclick="addProductToOrder(${product.id})">
                                    <img src="${imagePath}" class="product-img" alt="${product.name}" onerror="this.src='{{ asset("/images/default-product.png") }}'">
                                    <div class="p-2 flex flex-col justify-between flex-1 bg-white">
                                        <div class="product-name" title="${product.name}">${product.name}</div>
                                        <div class="text-[#F47716] font-mono font-bold text-xs mt-1">${formatMoney(product.price_sale || 0)}</div>
                                    </div>
                                </div>
                            `;
                        });
                        $('#productsList').html(html);
                    }
                },
                error: function() {
                    toastr.error('Không thể tải danh sách sản phẩm');
                }
            });
        }

        // Display Order Items
        function displayOrderItems() {
            let html = '';
            let total = 0;

            // 1. Cộng tổng các món đã lưu (hiển thị trong accordion)
            if (existingOrderDetails && existingOrderDetails.length > 0) {
                existingOrderDetails.forEach(function(item) {
                    const price = item.price || 0;
                    const qty = item.qty || 0;
                    total += price * qty;
                });
            }

            // 2. Render Current Items (Editable)
            if (currentOrderDetails && currentOrderDetails.length > 0) {
                if (existingOrderDetails && existingOrderDetails.length > 0) {
                     html += '<div class="text-[#1890FF] text-[11px] mb-2 mt-1 font-semibold uppercase tracking-wider border-b border-[#F0F0F0] pb-1">Món vừa chọn (Chưa báo bếp)</div>';
                }
                
                currentOrderDetails.forEach(function(item) {
                    const itemTotal = item.price * item.qty;
                    total += itemTotal;
                    html += `
                        <div class="py-1.5 border-b border-[#F0F0F0] last:border-0 flex items-center justify-between text-xs bg-white px-2 rounded mb-1 border border-blue-100">
                            <div class="flex-1 pr-2">
                                <div class="font-medium text-[rgba(0,0,0,0.85)]">${item.product_name}</div>
                                <div class="text-[11px] text-[rgba(0,0,0,0.45)] font-mono">${formatMoney(item.price)} × ${item.qty}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="font-mono font-semibold text-[rgba(0,0,0,0.85)]">${formatMoney(itemTotal)}</div>
                                <div class="flex items-center border border-[#D9D9D9] rounded overflow-hidden">
                                    <button class="order-stepper-btn" onclick="updateQty(${item.product_id}, ${item.qty - 1})">-</button>
                                    <span class="w-6 h-5.5 bg-[#FAFAFA] flex items-center justify-center text-[11px] font-mono border-x border-[#D9D9D9]">${item.qty}</span>
                                    <button class="order-stepper-btn" onclick="updateQty(${item.product_id}, ${item.qty + 1})">+</button>
                                </div>
                                <button class="text-gray-400 hover:text-red-500 transition-colors p-1" onclick="removeItem(${item.product_id})" title="Bỏ chọn">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        </div>
                    `;
                });
            } 
            
            $('#orderItemsContent').html(html);
            $('#totalAmountDisplay').text(formatMoney(total));
            
            // Cập nhật trạng thái nút Thanh toán, In hoá đơn và Báo bếp
            const hasItemsNow = hasExistingItems || (currentOrderDetails && currentOrderDetails.length > 0);
            if (hasItemsNow) {
                $('#payBtn').removeClass('disabled').css('pointer-events', 'auto');
                $('#printBillBtn').removeClass('disabled').prop('disabled', false).css('pointer-events', 'auto');
            } else {
                $('#payBtn').addClass('disabled').css('pointer-events', 'none');
                $('#printBillBtn').addClass('disabled').prop('disabled', true).css('pointer-events', 'none');
            }

            $('#printKitchenBtn').prop('disabled', !hasItemsNow);
        }

        function addProductToOrder(productId) {
            const product = allProducts.find(p => p.id === productId);
            if (!product) {
                toastr.error('Không tìm thấy thông tin sản phẩm');
                return;
            }

            const existingItem = currentOrderDetails.find(item => item.product_id === productId);
            if (existingItem) {
                existingItem.qty += 1;
            } else {
                currentOrderDetails.push({
                    product_id: product.id,
                    qty: 1,
                    price: product.price_sale || 0,
                    product_name: product.name
                });
            }

            isOrderSaved = false;
            displayOrderItems();
        }

        function updateQty(productId, newQty) {
            if (newQty < 1) {
                removeItem(productId);
                return;
            }

            const item = currentOrderDetails.find(item => item.product_id === productId);
            if (item) {
                item.qty = newQty;
                isOrderSaved = false;
                displayOrderItems();
            }
        }

        function removeItem(productId) {
            if (!confirm('Bạn có chắc muốn xóa món này?')) {
                return;
            }

            currentOrderDetails = currentOrderDetails.filter(item => item.product_id !== productId);
            isOrderSaved = false;
            displayOrderItems();
        }

        function decrementHistory(detailId) {
            const qtyEl = document.getElementById('history-qty-' + detailId);
            if (!qtyEl) return;
            const qty = parseInt(qtyEl.textContent, 10) || 0;
            if (qty <= 0) return;
            if (!confirm('Bạn có chắc muốn trừ 1 món này?')) return;
            const newQty = qty - 1;
            if (newQty < 1) {
                deleteHistory(detailId);
                return;
            }
            $.ajax({
                url: '{{ route("admin.table-order.updateProductQty") }}',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}', order_detail_id: detailId, qty: newQty },
                success: function(resp) {
                    if (resp.success) {
                        toastr.success('Đã cập nhật số lượng');
                        const amountEl = document.getElementById('history-amount-' + detailId);
                        const itemEl = document.getElementById('history-item-' + detailId);
                        qtyEl.textContent = String(newQty);
                        const price = parseFloat(itemEl?.getAttribute('data-price') || '0') || 0;
                        const newAmount = price * newQty;
                        if (amountEl) amountEl.textContent = formatMoney(newAmount);
                    } else {
                        toastr.error(resp.message || 'Cập nhật thất bại');
                    }
                },
                error: function(xhr) {
                    let msg = 'Có lỗi xảy ra';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    toastr.error(msg);
                }
            });
        }

        function deleteHistory(detailId) {
            if (!confirm('Bạn có chắc muốn xoá món này?')) return;
            $.ajax({
                url: '{{ route("admin.table-order.removeProductFromOrder") }}',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}', order_detail_id: detailId },
                success: function(resp) {
                    if (resp.success) {
                        toastr.success('Đã xoá món');
                        window.location.reload();
                    } else {
                        toastr.error(resp.message || 'Xoá thất bại');
                    }
                },
                error: function(xhr) {
                    let msg = 'Có lỗi xảy ra';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    toastr.error(msg);
                }
            });
        }

        // In trực tiếp thông qua iframe ẩn để không bị trình duyệt chặn popup
        function directPrintHTML(html) {
            let iframe = document.getElementById('receipt_print_iframe');
            if (!iframe) {
                iframe = document.createElement('iframe');
                iframe.id = 'receipt_print_iframe';
                iframe.style.position = 'fixed';
                iframe.style.right = '0';
                iframe.style.bottom = '0';
                iframe.style.width = '0';
                iframe.style.height = '0';
                iframe.style.border = '0';
                document.body.appendChild(iframe);
            }
            const doc = iframe.contentWindow.document;
            doc.open();
            doc.write(html);
            doc.close();
            iframe.contentWindow.focus();
            setTimeout(() => {
                try {
                    iframe.contentWindow.print();
                } catch(e) {
                    console.error('Lỗi khi in:', e);
                }
            }, 300);
        }

        function printKitchen() {
            if (!currentOrderId) return;
            
            // Collect all items to print: pending items first, then current order details
            let items = [];
            let stt = 1;
            
            // Add pending items from existingOrderDetails
            existingOrderDetails.forEach(item => {
                if (item.status === 'pending') {
                    items.push({stt: stt++, name: item.product ? item.product.name : 'Món ăn', qty: item.qty});
                }
            });
            
            // Add current order details (items staff added)
            if (currentOrderDetails && currentOrderDetails.length > 0) {
                currentOrderDetails.forEach(item => {
                    items.push({stt: stt++, name: item.product_name, qty: item.qty});
                });
            }
            
            // If no pending/new items, print full list of items on table
            if (items.length === 0) {
                if (existingOrderDetails && existingOrderDetails.length > 0) {
                    existingOrderDetails.forEach(item => {
                        items.push({stt: stt++, name: item.product ? item.product.name : 'Món ăn', qty: item.qty});
                    });
                } else {
                    toastr.warning('Chưa có món nào để in báo bếp');
                    return;
                }
            }
            
            const note = $('#orderNote').val();
            
            // If we have current order details, save them first
            if (currentOrderDetails && currentOrderDetails.length > 0) {
                saveOrder(function() {
                    const html = generateKitchenTicketHTML({
                        floor: '{{ $table->floor->name ?? '' }}',
                        table: '{{ $table->name }}',
                        customer_name: @json($order->customer_name),
                        customer_phone: @json($order->customer_phone),
                        order_turn: orderTurn,
                        note: note,
                        items
                    });
                    directPrintHTML(html);
                    $.post('{{ route('admin.table-order.incrementOrderTurn') }}', { _token: '{{ csrf_token() }}', order_id: currentOrderId })
                        .always(function() { 
                            setTimeout(() => window.location.reload(), 1200); 
                        });
                });
            } else {
                // Just print existing items
                const html = generateKitchenTicketHTML({
                    floor: '{{ $table->floor->name ?? '' }}',
                    table: '{{ $table->name }}',
                    customer_name: @json($order->customer_name),
                    customer_phone: @json($order->customer_phone),
                    order_turn: orderTurn,
                    note: note,
                    items
                });
                directPrintHTML(html);
                toastr.success('Đã gửi lệnh in báo bếp');
            }
        }

        // Chức năng In hoá đơn tính tiền cho khách
        function printTableBill() {
            let billItems = [];
            let total = 0;
            let idx = 1;

            if (existingOrderDetails && existingOrderDetails.length > 0) {
                existingOrderDetails.forEach(item => {
                    const price = parseFloat(item.price) || 0;
                    const qty = parseInt(item.qty) || 1;
                    const amount = parseFloat(item.amount) || (price * qty);
                    total += amount;
                    billItems.push({
                        stt: idx++,
                        name: item.product ? item.product.name : 'Món ăn',
                        qty: qty,
                        price: price,
                        amount: amount
                    });
                });
            }

            if (currentOrderDetails && currentOrderDetails.length > 0) {
                currentOrderDetails.forEach(item => {
                    const price = parseFloat(item.price) || 0;
                    const qty = parseInt(item.qty) || 1;
                    const amount = price * qty;
                    total += amount;
                    billItems.push({
                        stt: idx++,
                        name: item.product_name || 'Món ăn',
                        qty: qty,
                        price: price,
                        amount: amount
                    });
                });
            }

            if (billItems.length === 0) {
                toastr.warning('Chưa có món nào để in hoá đơn');
                return;
            }

            const html = generateCustomerBillHTML({
                floor: '{{ $table->floor->name ?? '' }}',
                table: '{{ $table->name }}',
                customer_name: @json($order->customer_name),
                customer_phone: @json($order->customer_phone),
                order_id: currentOrderId,
                items: billItems,
                total: total
            });

            directPrintHTML(html);
            toastr.success('Đang mở trang in hoá đơn');
        }

        function generateCustomerBillHTML(data) {
            const { floor, table, customer_name, customer_phone, order_id, items, total } = data;
            const now = new Date();
            const pad = n => String(n).padStart(2, '0');
            const dateStr = `${pad(now.getDate())}/${pad(now.getMonth()+1)}/${now.getFullYear()} ${pad(now.getHours())}:${pad(now.getMinutes())}`;
            const nf = new Intl.NumberFormat('vi-VN');
            let rows = '';
            items.forEach((i, idx) => {
                rows += `
                    <tr>
                        <td style="text-align:center; padding: 5px 0; border-bottom: 1px dashed #bbb;">${idx + 1}</td>
                        <td style="padding: 5px 0; border-bottom: 1px dashed #bbb;">${i.name}</td>
                        <td style="text-align:center; padding: 5px 0; border-bottom: 1px dashed #bbb;">${i.qty}</td>
                        <td style="text-align:right; padding: 5px 0; border-bottom: 1px dashed #bbb;">${nf.format(i.price)} đ</td>
                        <td style="text-align:right; padding: 5px 0; border-bottom: 1px dashed #bbb; font-weight: bold;">${nf.format(i.amount)} đ</td>
                    </tr>
                `;
            });
            return `<!doctype html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>Hóa đơn - Bàn ${table}</title>
                <style>
                    @media print { @page { size: 80mm auto; margin: 3mm; } }
                    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; width: 74mm; margin: 0 auto; padding: 6px; font-size: 12px; color: #111; }
                    .header { text-align: center; border-bottom: 1px solid #111; padding-bottom: 6px; margin-bottom: 6px; }
                    .logo-title { font-size: 17px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
                    .sub-title { font-size: 11px; color: #444; margin-top: 2px; }
                    .bill-title { text-align: center; font-size: 15px; font-weight: 800; margin: 8px 0 6px 0; }
                    .info-line { display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 3px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 11px; }
                    th { text-align: left; padding: 4px 0; border-bottom: 1px solid #111; border-top: 1px solid #111; }
                    .total-section { margin-top: 8px; border-top: 1px solid #111; padding-top: 6px; }
                    .total-row { display: flex; justify-content: space-between; font-size: 14px; font-weight: 800; color: #d92510; }
                    .footer { margin-top: 12px; text-align: center; font-size: 10px; color: #666; border-top: 1px dashed #999; padding-top: 6px; }
                </style>
            </head>
            <body>
                <div class="header">
                    <div class="logo-title">NHÀ HÀNG CIVILIZE</div>
                    <div class="sub-title">Cụm 8, TT. Phúc Thọ, TP. Hà Nội</div>
                    <div class="sub-title">Hotline / Zalo: 0964.358.394</div>
                </div>
                <div class="bill-title">PHIẾU TÍNH TIỀN (TẠM TÍNH)</div>
                <div class="info-line"><span>Mã đơn: <strong>#${order_id}</strong></span><span>Ngày: ${dateStr}</span></div>
                <div class="info-line"><span>Khu vực: <strong>${floor}</strong></span><span>Bàn: <strong>${table}</strong></span></div>
                <div class="info-line"><span>Khách hàng: <strong>${customer_name}</strong></span><span>ĐT: ${customer_phone}</span></div>
                <table>
                    <thead>
                        <tr>
                            <th style="width:22px; text-align:center;">#</th>
                            <th>Tên món</th>
                            <th style="width:25px; text-align:center;">SL</th>
                            <th style="width:50px; text-align:right;">Đơn giá</th>
                            <th style="width:55px; text-align:right;">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rows}
                    </tbody>
                </table>
                <div class="total-section">
                    <div class="total-row">
                        <span>TỔNG CỘNG:</span>
                        <span>${nf.format(total)} ₫</span>
                    </div>
                </div>
                <div class="footer">
                    <div>Quý khách vui lòng kiểm tra lại món trước khi thanh toán.</div>
                    <div style="font-weight: 600; margin-top: 3px;">CIVILIZE XIN CHÂN THÀNH CẢM ƠN QUÝ KHÁCH!</div>
                </div>
            </body>
            </html>`;
        }

        function saveOrder(callback) {
            if (!currentOrderId) return;
            const note = $('#orderNote').val();

            $.ajax({
                url: '{{ route("admin.table-order.saveOrder") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    order_id: currentOrderId,
                    items: currentOrderDetails,
                    note: note
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        
                        // Optimistically update state
                        isOrderSaved = true;
                        hasExistingItems = true;
                        $('#payBtn').removeClass('disabled');

                        if (typeof callback === 'function') callback();
                    } else {
                        toastr.error(response.message || 'Lưu thất bại');
                    }
                },
                error: function(xhr) {
                    let msg = 'Có lỗi xảy ra';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    toastr.error(msg);
                }
            });
        }

        // Format Money
        function formatMoney(amount) {
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
        }

        function generateKitchenTicketHTML(data) {
            const { floor, table, customer_name, customer_phone, order_turn, note, items } = data;
            const now = new Date();
            const pad = n => String(n).padStart(2, '0');
            const dateStr = `${pad(now.getDate())}/${pad(now.getMonth()+1)}/${now.getFullYear()} ${pad(now.getHours())}:${pad(now.getMinutes())}`;
            let rows = '';
            items.forEach(i => {
                rows += `<tr><td style="text-align:center;width:30px;">${i.stt}</td><td>${i.name}</td><td style="text-align:right;width:50px;">${i.qty}</td></tr>`;
            });
            let noteHtml = note ? `<div class="meta" style="border:1px dashed #000; padding:5px; margin:5px 0;">Ghi chú: <strong>${note}</strong></div>` : '';
            return `<!doctype html><html><head><meta charset="utf-8"><title>Phiếu gọi món</title><style>
                @media print { @page { size: 80mm auto; margin: 5mm; } }
                body { font-family: Arial, sans-serif; width: 80mm; }
                .title { text-align:center; font-size:16px; font-weight:bold; margin-bottom:6px; }
                .meta { font-size:12px; margin-bottom:6px; }
                table { width:100%; border-collapse: collapse; font-size:12px; }
                th, td { padding:4px 0; border-bottom:1px dashed #000; }
                th { text-align:left; border-top:1px dashed #000; }
                .footer { margin-top:8px; font-size:11px; text-align:center; }
            </style></head><body>
                <div class="title">PHIẾU GỌI MÓN</div>
                <div class="meta">Tầng: <strong>${floor}</strong> | Bàn: <strong>${table}</strong></div>
                <div class="meta">Lần order: <strong>${order_turn}</strong> | Thời gian: ${dateStr}</div>
                <div class="meta">Khách: <strong>${customer_name}</strong> | ĐT: ${customer_phone}</div>
                ${noteHtml}
                <div class="table-responsive"><table><thead><tr><th style="width:30px; text-align:center;">STT</th><th>Món</th><th style="width:50px; text-align:right;">SL</th></tr></thead><tbody>${rows}</tbody></table></div>
                <div class="footer">In từ hệ thống nhà hàng</div>
            </body></html>`;
        }

        // Chức năng Huỷ bàn
        function confirmCancelTable() {
            if (!confirm('Bạn có chắc muốn huỷ bàn này?')) return;
            
            $.ajax({
                url: '{{ route("admin.table-order.cancelTable") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    order_id: currentOrderId
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('Huỷ bàn thành công');
                        window.location.href = '{{ route("admin.table-order.index") }}';
                    } else {
                        toastr.error(response.message || 'Huỷ bàn thất bại');
                    }
                },
                error: function(xhr) {
                    let msg = 'Có lỗi xảy ra';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    toastr.error(msg);
                }
            });
        }

        // Chức năng Đổi bàn
        function showChangeTableModal() {
            selectedNewTableId = null;
            $('#confirmChangeTableBtn').prop('disabled', true);
            $('#changeTableModal .change-table-item').removeClass('selected');
            $('#changeTableModal').modal('show');
        }

        function selectTable(tableId) {
            selectedNewTableId = tableId;
            $('#changeTableModal .change-table-item').removeClass('selected');
            $('#changeTableModal .change-table-item[data-table-id="' + tableId + '"]').addClass('selected');
            $('#confirmChangeTableBtn').prop('disabled', false);
        }

        function confirmChangeTable() {
            if (!selectedNewTableId) return;
            
            $.ajax({
                url: '{{ route("admin.table-order.changeTable") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    order_id: currentOrderId,
                    new_table_id: selectedNewTableId
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('Đổi bàn thành công');
                        window.location.href = '{{ route("admin.table-order.order", ':tableId') }}'.replace(':tableId', selectedNewTableId);
                    } else {
                        toastr.error(response.message || 'Đổi bàn thất bại');
                    }
                },
                error: function(xhr) {
                    let msg = 'Có lỗi xảy ra';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    toastr.error(msg);
                }
            });
        }

        function markItemAsServed(orderDetailId) {
            if (!confirm('Đánh dấu món ăn này là đã lên món?')) return;
            $.ajax({
                url: '{{ route("admin.table-order.markItemAsServed") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    order_detail_id: orderDetailId
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        window.location.reload();
                    } else {
                        toastr.error(response.message || 'Cập nhật thất bại');
                    }
                },
                error: function(xhr) {
                    let msg = 'Có lỗi xảy ra';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    toastr.error(msg);
                }
            });
        }
    </script>
</x-layout-backend>
