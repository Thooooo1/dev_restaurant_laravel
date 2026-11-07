<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use App\Models\Table;
use App\Models\Booking;
use App\Models\Order;
use App\Models\ChatbotMessage;
use App\Models\CustomerAiProfile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * ChatbotService - CIVILIZE AI Orchestrator
 * 
 * 6 Intelligent Pillars:
 * 1. NLU & Intent Extraction
 * 2. Reasoning with Business Guardrails
 * 3. 2-Tier Memory (Short-term conversation history + Long-term customer profile)
 * 4. Hybrid RAG (Structured SQL for Menu/Tables + Unstructured Knowledge for Policies)
 * 5. Native Tool Calling (search_menu, check_table, create_booking, track_order)
 * 6. Continuous Evaluation & Feedback
 */
class ChatbotService
{
    /**
     * Unstructured Knowledge Base Chunks (Policies, Amenities, Facilities)
     */
    protected array $knowledgeBase = [
        'general_info' => [
            'title' => 'Tổng quan nhà hàng',
            'keywords' => ['nhà hàng civilize', 'về civilize', 'giới thiệu', 'thương hiệu', 'phong cách ẩm thực', 'sứ mệnh'],
            'content' => 'CIVILIZE Restaurant là nhà hàng ẩm thực Việt & Á đương đại tại số 120 Phúc Hòa, Phúc Thọ, Hà Nội. Tôn chỉ hoạt động luôn đặt chữ Tâm và chữ Tín lên hàng đầu, mang đến không gian ấm cúng, sang trọng cùng những món ăn tinh hoa được chế biến tỉ mỉ từ nguyên liệu tươi sạch mỗi ngày.'
        ],
        'location_and_hours' => [
            'title' => 'Địa chỉ & Giờ mở cửa',
            'keywords' => ['địa chỉ', 'ở đâu', 'chỗ nào', 'vị trí', 'đường đi', 'tìm quán', 'giờ mở cửa', 'mấy giờ', 'mở cửa', 'đóng cửa', 'thời gian phục vụ'],
            'content' => 'Nhà hàng CIVILIZE mở cửa phục vụ từ 07:00 sáng đến 22:30 tối tất cả các ngày trong tuần (kể cả Thứ 7, Chủ Nhật và các ngày Lễ, Tết). Địa chỉ chính xác tại: 120 Phúc Hòa, Huyện Phúc Thọ, TP. Hà Nội.'
        ],
        'hotline_and_contact' => [
            'title' => 'Hotline & Liên hệ',
            'keywords' => ['hotline', 'số điện thoại', 'sđt', 'liên hệ', 'gọi điện', 'điện thoại', 'chăm sóc khách hàng'],
            'content' => 'Hotline hỗ trợ và đặt bàn nhanh 24/7 của CIVILIZE Restaurant là: 0338 990 538. Quý khách có thể gọi trực tiếp bất cứ lúc nào để được hỗ trợ vị trí đẹp hoặc giải đáp thắc mắc.'
        ],
        'parking_facilities' => [
            'title' => 'Bãi đỗ xe máy & Ô tô',
            'keywords' => ['đỗ xe', 'gửi xe', 'bãi xe', 'đậu xe', 'chỗ để xe', 'ô tô', 'xe máy', 'chỗ đỗ ô tô', 'xe bán tải', 'xe du lịch'],
            'content' => 'CIVILIZE Restaurant có bãi đỗ xe riêng biệt siêu rộng rãi cho cả xe máy và ô tô (kể cả xe bán tải, xe du lịch 16-45 chỗ). Bãi xe hoàn toàn MIỄN PHÍ, có đội ngũ bảo vệ trực an ninh trông giữ 24/24.'
        ],
        'reservation_policy' => [
            'title' => 'Chính sách đặt bàn & Ưu đãi',
            'keywords' => ['ưu đãi đặt bàn', 'giảm giá đặt bàn', 'khuyến mãi', 'chiết khấu đặt bàn', 'giữ bàn bao lâu'],
            'content' => 'Khi đặt bàn trước qua chatbot hoặc website, quý khách được GIẢM NGAY 10% trên tổng hóa đơn món ăn. Nhà hàng sẽ giữ bàn tối đa 15-30 phút so với giờ hẹn để đảm bảo chất lượng phục vụ tốt nhất.'
        ],
        'vip_rooms' => [
            'title' => 'Phòng VIP & Không gian riêng tư',
            'keywords' => ['phòng vip', 'phòng riêng', 'riêng tư', 'tiếp khách', 'đối tác', 'cách âm', 'máy chiếu'],
            'content' => 'Nhà hàng có hệ thống phòng VIP cách âm cao cấp sức chứa linh hoạt từ 8 đến 50 khách. Phòng trang bị điều hòa riêng, nội thất gỗ sang trọng, màn hình trình chiếu và nhân viên phục vụ riêng biệt, thích hợp để tiếp khách đối tác hoặc họp mặt gia đình.'
        ],
        'events_and_parties' => [
            'title' => 'Tổ chức tiệc & Sự kiện sinh nhật',
            'keywords' => ['tiệc', 'sinh nhật', 'liên hoan', 'thôi nôi', 'họp lớp', 'sự kiện', 'trang trí', 'bóng bay', 'bánh kem'],
            'content' => 'CIVILIZE nhận tổ chức trọn gói tiệc sinh nhật, thôi nôi, liên hoan công ty. Nhà hàng hỗ trợ gói trang trí tiệc theo yêu cầu hoàn toàn MIỄN PHÍ. Với đoàn từ 15 người trở lên đặt trước, nhà hàng tặng kèm bánh sinh nhật hoặc hoa tươi chúc mừng.'
        ],
        'corkage_and_outside_drinks' => [
            'title' => 'Quy định mang đồ uống & Rượu ngoài vào',
            'keywords' => ['mang rượu', 'rượu ngoài', 'mang đồ uống', 'phí mở chai', 'phụ thu', 'phí phục vụ rượu', 'rượu vang'],
            'content' => 'Quý khách hoàn toàn có thể mang theo rượu kỷ niệm vào dùng tiệc. Nhà hàng chỉ áp dụng mức phụ thu mở chai và phục vụ ly đá ướp lạnh siêu ưu đãi (50.000₫ – 100.000₫/chai tùy loại). Nếu quý khách dùng rượu truyền thống của quán thì không tính phí.'
        ],
        'food_safety_hygiene' => [
            'title' => 'Vệ sinh an toàn thực phẩm & Nguồn gốc',
            'keywords' => ['vệ sinh', 'an toàn thực phẩm', 'nguồn gốc', 'tươi sạch', 'nguyên liệu', 'vietgap'],
            'content' => '100% nguồn rau củ, thịt và hải sản tại CIVILIZE đều được tuyển chọn khắt khe mỗi sáng từ các đối tác đạt chuẩn VietGAP. Bếp thiết kế khép kín một chiều, được chứng nhận đầy đủ tiêu chuẩn vệ sinh ATTP của Sở Y tế.'
        ],
        'delivery_and_shipping' => [
            'title' => 'Giao hàng tận nơi & Phí ship',
            'keywords' => ['giao hàng', 'ship', 'mang về', 'phí ship', 'freeship', 'bao lâu'],
            'content' => 'Nhà hàng phục vụ giao món tận nơi siêu tốc trong vòng 30–45 phút với hộp giữ nhiệt chuyên dụng. MIỄN PHÍ vận chuyển toàn bộ cho đơn hàng từ 599.000₫ trong bán kính 5km.'
        ],
        'payment_methods' => [
            'title' => 'Phương thức thanh toán',
            'keywords' => ['thanh toán', 'chuyển khoản', 'tiền mặt', 'cod', 'quẹt thẻ', 'vietqr', 'hóa đơn vat'],
            'content' => 'Quý khách có thể thanh toán bằng Tiền mặt khi nhận hàng (COD), quẹt thẻ ngân hàng tại quầy (Visa, Mastercard, ATM) hoặc quét mã VietQR tự động. Nhà hàng hỗ trợ xuất hóa đơn VAT điện tử.'
        ],
    ];

    /**
     * Tool Definitions for Gemini 1.5 Flash Function Calling
     */
    protected function getToolDeclarations(): array
    {
        return [
            [
                'name' => 'search_menu_dishes',
                'description' => 'Tìm kiếm các món ăn thực tế có trong database nhà hàng kèm giá tiền và danh mục chính xác. BẮT BUỘC dùng tool này khi khách hỏi về món ăn, giá tiền, hoặc cần tư vấn chọn món. Để xem các món đặc sản / nổi bật của quán, có thể để trống keyword hoặc truyền category_name là lẩu, nướng...',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'keyword' => [
                            'type' => 'STRING',
                            'description' => 'Tên món ăn hoặc từ khóa cụ thể (ví dụ: lẩu, gà, nướng, bò, tôm...). Nếu khách hỏi chung chung hoặc hỏi đặc sản / món ngon thì để trống từ khóa này.'
                        ],
                        'category_name' => [
                            'type' => 'STRING',
                            'description' => 'Tên loại món (ví dụ: lẩu, nướng, khai vị, đồ xào, đồ chiên, đồ uống...)'
                        ],
                        'max_price' => [
                            'type' => 'NUMBER',
                            'description' => 'Mức giá tối đa cho một món (VND)'
                        ],
                        'dietary' => [
                            'type' => 'STRING',
                            'description' => 'Yêu cầu ăn kiêng: không cay, chay, thanh đạm, cho trẻ em'
                        ]
                    ]
                ]
            ],
            [
                'name' => 'check_table_availability',
                'description' => 'Kiểm tra tình trạng bàn trống thực tế của nhà hàng theo số người, ngày và giờ. BẮT BUỘC gọi tool này trước khi trả lời khách xem còn bàn hay không.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'number_of_people' => [
                            'type' => 'INTEGER',
                            'description' => 'Số lượng khách đi ăn (ví dụ: 4, 6, 10)'
                        ],
                        'booking_date' => [
                            'type' => 'STRING',
                            'description' => 'Ngày đặt bàn theo định dạng YYYY-MM-DD (ví dụ: 2026-11-01)'
                        ],
                        'booking_time' => [
                            'type' => 'STRING',
                            'description' => 'Giờ đặt bàn theo định dạng HH:MM (ví dụ: 19:00)'
                        ]
                    ],
                    'required' => ['number_of_people']
                ]
            ],
            [
                'name' => 'confirm_and_create_booking',
                'description' => 'LƯU ĐƠN ĐẶT BÀN VÀO HỆ THỐNG. CHỈ ĐƯỢC GỌI KHI KHÁCH ĐÃ ĐỒNG Ý XÁC NHẬN VÀ CUNG CẤP ĐỦ TÊN, SĐT, SỐ KHÁCH, NGÀY VÀ GIỜ. Không được tự ý gọi tool này khi khách mới chỉ đang hỏi giờ.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'name' => [
                            'type' => 'STRING',
                            'description' => 'Họ và tên khách hàng đặt bàn'
                        ],
                        'phone' => [
                            'type' => 'STRING',
                            'description' => 'Số điện thoại liên hệ'
                        ],
                        'number_of_people' => [
                            'type' => 'INTEGER',
                            'description' => 'Số lượng khách'
                        ],
                        'booking_date' => [
                            'type' => 'STRING',
                            'description' => 'Ngày dùng bữa (YYYY-MM-DD)'
                        ],
                        'booking_time' => [
                            'type' => 'STRING',
                            'description' => 'Giờ dùng bữa (HH:MM)'
                        ],
                        'note' => [
                            'type' => 'STRING',
                            'description' => 'Ghi chú yêu cầu đặc biệt (phòng VIP, ghế trẻ em, tiệc sinh nhật...)'
                        ]
                    ],
                    'required' => ['name', 'phone', 'number_of_people', 'booking_date', 'booking_time']
                ]
            ],
            [
                'name' => 'lookup_order_status',
                'description' => 'Tra cứu thông tin và trạng thái tiến độ đơn hàng online/giao món của khách hàng theo số điện thoại hoặc mã đơn.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'phone' => [
                            'type' => 'STRING',
                            'description' => 'Số điện thoại đặt đơn'
                        ],
                        'order_id' => [
                            'type' => 'INTEGER',
                            'description' => 'Mã đơn hàng (nếu khách cung cấp)'
                        ]
                    ]
                ]
            ],
            [
                'name' => 'get_restaurant_policy',
                'description' => 'Tra cứu chính sách, tiện ích và cơ sở vật chất của nhà hàng (bãi đỗ xe, phụ thu mang rượu, phòng VIP, tiệc sinh nhật, giờ mở cửa, địa chỉ...).',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'topic' => [
                            'type' => 'STRING',
                            'description' => 'Chủ đề cần tra cứu: parking, outside_drinks, vip_rooms, events_and_parties, reservation_policy, location_and_hours, food_safety, delivery_and_shipping, payment_methods'
                        ]
                    ],
                    'required' => ['topic']
                ]
            ],
            [
                'name' => 'call_waiter_service',
                'description' => 'GỌI NHÂN VIÊN PHỤC VỤ HOẶC YÊU CẦU HỖ TRỢ TẠI BÀN. Dùng khi khách muốn gọi nhân viên tới bàn, xin thêm đá, nước, gia vị, khăn lạnh, hoặc yêu cầu tính tiền tại chỗ.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'table_name_or_number' => [
                            'type' => 'STRING',
                            'description' => 'Số bàn hoặc tên bàn của khách (ví dụ: Bàn 3, Bàn 12... nếu khách có nhắc tới)'
                        ],
                        'call_type' => [
                            'type' => 'STRING',
                            'description' => 'Loại hỗ trợ: call_waiter (gọi nhân viên), water (thêm đá/nước), cutlery (bát đũa/khăn), bill (thanh toán), other (khác)'
                        ],
                        'note' => [
                            'type' => 'STRING',
                            'description' => 'Chi tiết yêu cầu của khách'
                        ]
                    ],
                    'required' => ['note']
                ]
            ]
        ];
    }

    /**
     * Tool Execution Engine: Direct MySQL & Eloquent Queries
     */
    public function executeTool(string $name, array $args, array $context = []): array
    {
        switch ($name) {
            case 'call_waiter_service':
                return $this->toolCallWaiterService($args, $context);

            case 'search_menu_dishes':
                return $this->toolSearchMenuDishes($args);

            case 'check_table_availability':
                return $this->toolCheckTableAvailability($args);

            case 'confirm_and_create_booking':
                return $this->toolConfirmAndCreateBooking($args, $context);

            case 'lookup_order_status':
                return $this->toolLookupOrderStatus($args, $context);

            case 'get_restaurant_policy':
                return $this->toolGetRestaurantPolicy($args);

            default:
                return ['error' => "Không tìm thấy công cụ: {$name}"];
        }
    }

    /**
     * Resolve category names / synonyms to array of category IDs
     */
    protected function resolveCategoryIds(string $categoryInput): array
    {
        $input = mb_strtolower(trim($categoryInput), 'UTF-8');
        if (empty($input)) return [];

        if (str_contains($input, 'uống') || str_contains($input, 'nước') || str_contains($input, 'giải khát') || str_contains($input, 'beverage') || str_contains($input, 'drink')) {
            return [13, 15, 16, 18, 19]; // Bia rượu, Nước ngọt, Sinh tố, Nước ép, Cafe
        }
        if (str_contains($input, 'lẩu') || str_contains($input, 'om') || str_contains($input, 'hotpot')) {
            return [8]; // Lẩu + đồ om
        }
        if (str_contains($input, 'nướng') || str_contains($input, 'bbq') || str_contains($input, 'grill')) {
            return [12]; // Đồ nướng
        }
        if (str_contains($input, 'khai vị') || str_contains($input, 'appetizer') || str_contains($input, 'salad')) {
            return [1]; // Khai vị
        }
        if (str_contains($input, 'chiên') || str_contains($input, 'rán') || str_contains($input, 'fried')) {
            return [11]; // Đồ chiên
        }
        if (str_contains($input, 'xào')) {
            return [9]; // Đồ xào
        }
        if (str_contains($input, 'rau') || str_contains($input, 'luộc') || str_contains($input, 'canh')) {
            return [10]; // Rau luộc
        }
        if (str_contains($input, 'ăn vặt')) {
            return [17]; // Ăn vặt
        }
        if (str_contains($input, 'bia') || str_contains($input, 'rượu')) {
            return [13]; // Bia rượu
        }
        if (str_contains($input, 'cafe') || str_contains($input, 'cà phê')) {
            return [19]; // Cafe
        }
        if (str_contains($input, 'sinh tố')) {
            return [16]; // Sinh tố
        }
        if (str_contains($input, 'nước ép') || str_contains($input, 'hoa quả')) {
            return [18]; // Nước ép
        }
        if (str_contains($input, 'nước ngọt') || str_contains($input, 'coca') || str_contains($input, 'pepsi')) {
            return [15]; // Nước ngọt
        }

        return Category::where('status', 1)
            ->where(function ($q) use ($input) {
                $q->where('name', 'LIKE', "%{$input}%");
            })
            ->pluck('id')
            ->toArray();
    }

    /**
     * TOOL 1: Search Menu Dishes from MySQL
     */
    protected function toolSearchMenuDishes(array $args): array
    {
        $rawKeyword = trim($args['keyword'] ?? '');
        $categoryName = trim($args['category_name'] ?? '');
        $maxPrice = !empty($args['max_price']) ? (float)$args['max_price'] : null;
        $dietary = trim($args['dietary'] ?? '');

        $lowerKw = mb_strtolower($rawKeyword, 'UTF-8');

        // Danh sách từ khóa thể hiện ý định gợi ý / khám phá / đặc sản tổng quát
        $exploratoryWords = [
            'đặc sản', 'dac san', 'món ngon', 'mon ngon', 'hôm nay có gì', 'hom nay co gi',
            'có gì', 'co gi', 'gợi ý', 'goi y', 'bestseller', 'bán chạy', 'ban chay',
            'nổi bật', 'noi bat', 'signature', 'ẩm thực', 'am thuc', 'ăn gì', 'an gi',
            'món hot', 'mon hot', 'thực đơn', 'thuc don', 'menu'
        ];

        $isExploratory = empty($rawKeyword);
        if (!$isExploratory) {
            foreach ($exploratoryWords as $ew) {
                if ($lowerKw === $ew || str_starts_with($lowerKw, $ew . ' ') || str_ends_with($lowerKw, ' ' . $ew)) {
                    $isExploratory = true;
                    break;
                }
            }
        }

        // Nếu là yêu cầu gợi ý món / đặc sản / câu hỏi mở mà không chỉ định danh mục cụ thể
        if ($isExploratory && empty($categoryName)) {
            // Lấy 4 món signature tiêu biểu nhất đại diện cho cả Lẩu & Nướng
            // ID 52: Lẩu thái, ID 53: Lẩu bắp bò nhúng dấm, ID 90: Bò la hủ nướng tảng, ID 86: Sườn nướng BBQ+Kim chi
            $signatureDishes = Product::where('status', 1)
                ->whereIn('id', [52, 53, 90, 86])
                ->get();

            if ($signatureDishes->count() < 4) {
                $signatureDishes = Product::where('status', 1)
                    ->whereIn('category_id', [8, 12])
                    ->orderBy('price_buy', 'desc')
                    ->limit(4)
                    ->get();
            }

            return [
                'found' => true,
                'is_recommendation' => true,
                'message' => 'Dưới đây là các món ăn đặc sản và signature được yêu thích nhất tại CIVILIZE:',
                'dishes' => $this->formatDishesForTool($signatureDishes)
            ];
        }

        $query = Product::where('status', 1);

        // 1. Resolve Category IDs
        $matchedCategoryIds = [];
        if (!empty($categoryName)) {
            $matchedCategoryIds = $this->resolveCategoryIds($categoryName);
            if (!empty($matchedCategoryIds)) {
                $query->whereIn('category_id', $matchedCategoryIds);
            }
        }

        // 2. Keyword Search
        if (!empty($rawKeyword) && !$isExploratory) {
            // Nếu keyword là từ khóa danh mục (ví dụ "món nướng", "lẩu", "nước uống") mà chưa có category_name
            $kwCatIds = empty($matchedCategoryIds) ? $this->resolveCategoryIds($rawKeyword) : [];
            if (!empty($kwCatIds)) {
                $matchedCategoryIds = $kwCatIds;
                $query->whereIn('category_id', $kwCatIds);
            } else {
                $words = explode(' ', $rawKeyword);
                $query->where(function ($q) use ($rawKeyword, $words) {
                    $q->where('name', 'LIKE', "%{$rawKeyword}%")
                      ->orWhere('description', 'LIKE', "%{$rawKeyword}%");
                    foreach ($words as $w) {
                        if (mb_strlen($w, 'UTF-8') >= 2) {
                            $q->orWhere('name', 'LIKE', "%{$w}%");
                        }
                    }
                });
            }
        }

        // 3. LOẠI TRỪ ĐỒ UỐNG / THUỐC LÁ trừ khi khách chủ động hỏi về đồ uống
        $drinkCategories = [13, 15, 16, 18, 19];
        $isDrinkQuery = false;
        if (!empty($matchedCategoryIds) && count(array_intersect($matchedCategoryIds, $drinkCategories)) > 0) {
            $isDrinkQuery = true;
        }
        $drinkKeywords = ['uống', 'nước', 'bia', 'rượu', 'cafe', 'cà phê', 'sinh tố', 'nước ép', 'trà', 'coca', 'pepsi'];
        foreach ($drinkKeywords as $dk) {
            if (str_contains($lowerKw, $dk)) {
                $isDrinkQuery = true;
                break;
            }
        }

        if (!$isDrinkQuery && empty($matchedCategoryIds)) {
            $query->whereNotIn('category_id', [13, 14, 15, 16, 18, 19]);
        }

        if ($maxPrice && $maxPrice > 0) {
            $query->where(function ($q) use ($maxPrice) {
                $q->where(function ($sq) use ($maxPrice) {
                    $sq->whereNotNull('price_sale')
                       ->where('price_sale', '>', 0)
                       ->where('price_sale', '<=', $maxPrice);
                })->orWhere(function ($sq) use ($maxPrice) {
                    $sq->where(function ($ssq) {
                        $ssq->whereNull('price_sale')->orWhere('price_sale', '<=', 0);
                    })->where('price_buy', '<=', $maxPrice);
                });
            });
        }

        $items = $query->orderBy('price_buy', 'desc')->limit(6)->get();

        // Nếu từ khóa cụ thể không tìm thấy món nào (ví dụ: pizza, sushi...)
        if ($items->isEmpty()) {
            $fallback = Product::where('status', 1)
                ->whereIn('category_id', [8, 12, 11, 1]) // Ưu tiên Lẩu, Nướng, Đồ chiên, Khai vị
                ->orderBy('price_buy', 'desc')
                ->limit(4)
                ->get();
            return [
                'found' => false,
                'message' => "Món này hiện chưa có trong thực đơn, nhà hàng xin gợi ý các món ăn đặc sắc được yêu thích nhất:",
                'dishes' => $this->formatDishesForTool($fallback)
            ];
        }

        return [
            'found' => true,
            'count' => $items->count(),
            'dishes' => $this->formatDishesForTool($items)
        ];
    }

    /**
     * TOOL 2: Check Table Availability from MySQL Tables & Bookings
     */
    protected function toolCheckTableAvailability(array $args): array
    {
        $people = (int)($args['number_of_people'] ?? 2);
        if ($people <= 0) $people = 2;

        $date = !empty($args['booking_date']) ? $args['booking_date'] : Carbon::today()->toDateString();
        $time = !empty($args['booking_time']) ? $args['booking_time'] : '19:00';

        // 1. Kiểm tra tổng số bàn trong nhà hàng có sức chứa phù hợp
        $matchingTables = Table::where('status', 1)
            ->where('seats', '>=', $people)
            ->get();

        if ($matchingTables->isEmpty()) {
            // Trường hợp khách quá đông (ví dụ > 30 người) -> cần ghép bàn hoặc phòng VIP
            return [
                'available' => true,
                'is_large_group' => true,
                'message' => "Đoàn của mình đi {$people} khách. Nhà hàng có hệ thống phòng tiệc VIP và không gian hội trường lớn sẵn sàng ghép bàn rộng rãi để phục vụ quý đoàn chu đáo nhất ạ."
            ];
        }

        // 2. Kiểm tra số lượng booking trùng khung giờ (trong vòng 2 tiếng)
        try {
            $bookingTimeCarbon = Carbon::parse("{$date} {$time}");
            $startWindow = $bookingTimeCarbon->copy()->subHours(2)->toTimeString();
            $endWindow = $bookingTimeCarbon->copy()->addHours(2)->toTimeString();

            $overlappingBookings = Booking::where('booking_date', $date)
                ->where('status', 1)
                ->whereBetween('booking_time', [$startWindow, $endWindow])
                ->count();
        } catch (\Exception $e) {
            $overlappingBookings = 0;
        }

        $availableCount = max(0, $matchingTables->count() - $overlappingBookings);

        return [
            'available' => ($availableCount > 0),
            'party_size' => $people,
            'requested_date' => $date,
            'requested_time' => $time,
            'matching_tables_count' => $matchingTables->count(),
            'current_booked_count' => $overlappingBookings,
            'status' => ($availableCount > 0) ? 'con_ban' : 'sap_het_ban',
            'message' => ($availableCount > 0)
                ? "Dạ ngày {$date} lúc {$time} nhà hàng vẫn còn bàn đẹp phù hợp cho {$people} khách ạ. Khi đặt trước anh chị được GIẢM NGAY 10% tổng hóa đơn món ăn."
                : "Dạ khung giờ {$time} ngày {$date} đang có khá nhiều khách đặt chỗ. Em vẫn có thể hỗ trợ sắp xếp bàn ưu tiên cho mình nếu anh chị chốt giữ bàn sớm nhé ạ!"
        ];
    }

    /**
     * TOOL 3: Confirm and Create Booking in MySQL
     */
    protected function toolConfirmAndCreateBooking(array $args, array $context = []): array
    {
        $name = trim($args['name'] ?? '');
        $phone = trim($args['phone'] ?? '');
        $people = (int)($args['number_of_people'] ?? 2);
        $date = trim($args['booking_date'] ?? Carbon::today()->toDateString());
        $time = trim($args['booking_time'] ?? '19:00');
        $note = trim($args['note'] ?? 'Đặt bàn qua Chatbot AI CIVILIZE');

        if (empty($name) || empty($phone)) {
            return [
                'success' => false,
                'error' => 'Vui lòng cung cấp đầy đủ Họ tên và Số điện thoại để hoàn tất đặt bàn.'
            ];
        }

        // Tạo đơn đặt bàn thật trong DB
        $booking = Booking::create([
            'name' => $name,
            'email' => $context['user']['email'] ?? "{$phone}@civilize.local",
            'phone' => $phone,
            'number_of_people' => $people,
            'booking_date' => $date,
            'booking_time' => $time,
            'note' => $note,
            'status' => 1 // 1: Chờ xác nhận / Đã tiếp nhận
        ]);

        // Cập nhật Long-term Memory cho khách
        $this->updateCustomerProfile($phone, [
            'customer_name' => $name,
            'user_id' => $context['user']['id'] ?? null,
            'total_visits_increment' => true
        ]);

        return [
            'success' => true,
            'booking_id' => $booking->id,
            'name' => $name,
            'phone' => $phone,
            'people' => $people,
            'date' => $date,
            'time' => $time,
            'discount' => '10% trên tổng hóa đơn món ăn',
            'message' => "ĐÃ TẠO ĐƠN ĐẶT BÀN THÀNH CÔNG (Mã: #{$booking->id}). Nhà hàng sẽ gọi điện thoại xác nhận trong ít phút."
        ];
    }

    /**
     * TOOL 4: Lookup Order Status from MySQL
     */
    protected function toolLookupOrderStatus(array $args, array $context = []): array
    {
        $phone = trim($args['phone'] ?? '');
        $orderId = !empty($args['order_id']) ? (int)$args['order_id'] : null;

        if (empty($phone) && empty($orderId) && !empty($context['user']['phone'])) {
            $phone = $context['user']['phone'];
        }

        if (empty($phone) && empty($orderId)) {
            return [
                'found' => false,
                'message' => 'Dạ anh chị vui lòng cho em xin Số điện thoại đặt hàng hoặc Mã đơn hàng để em tra cứu ngay ạ!'
            ];
        }

        $query = Order::query();
        if ($orderId) {
            $query->where('id', $orderId);
        } elseif ($phone) {
            $query->where('phone', $phone);
        }

        $order = $query->orderBy('created_at', 'desc')->first();

        if (!$order) {
            return [
                'found' => false,
                'message' => "Dạ em kiểm tra trên hệ thống chưa thấy đơn hàng nào khớp với thông tin cung cấp. Anh chị vui lòng kiểm tra lại số điện thoại hoặc gọi hotline 0338 990 538 để được hỗ trợ kiểm tra trực tiếp nhé ạ."
            ];
        }

        $statusText = match ((int)$order->status) {
            1 => 'Đang chuẩn bị món tại bếp',
            2 => 'Đang trên đường giao tới bạn',
            3 => 'Đã giao hàng thành công',
            0 => 'Đã hủy đơn',
            default => 'Đang xử lý'
        };

        return [
            'found' => true,
            'order_id' => $order->id,
            'customer_name' => $order->name,
            'phone' => $order->phone,
            'total_price' => number_format($order->total_price ?? 0, 0, ',', '.') . '₫',
            'status' => $statusText,
            'order_style' => $order->order_style ?? 'Giao tận nơi',
            'created_at' => $order->created_at->format('H:i - d/m/Y')
        ];
    }

    /**
     * TOOL 5: Get Restaurant Policy from Knowledge Base
     */
    protected function toolGetRestaurantPolicy(array $args): array
    {
        $topic = trim($args['topic'] ?? 'general_info');

        // Tìm kiếm chunk tri thức phù hợp
        foreach ($this->knowledgeBase as $key => $chunk) {
            if ($key === $topic || in_array($topic, $chunk['keywords'])) {
                return [
                    'topic' => $chunk['title'],
                    'content' => $chunk['content']
                ];
            }
        }

        return [
            'topic' => 'Quy định nhà hàng',
            'content' => $this->knowledgeBase['location_and_hours']['content']
        ];
    }

    /**
     * Tool: Gọi nhân viên phục vụ từ Chatbot
     */
    protected function toolCallWaiterService(array $args, array $context = []): array
    {
        $tableInput = trim($args['table_name_or_number'] ?? '');
        $callType = trim($args['call_type'] ?? 'call_waiter');
        $note = trim($args['note'] ?? 'Khách yêu cầu qua chatbot');

        // Tìm table_id từ session trước
        $tableId = session('table_id');
        $table = null;

        if ($tableId) {
            $table = \App\Models\Table::with('floor')->find($tableId);
        }

        // Nếu khách cung cấp tên/số bàn trong chat
        if (!$table && !empty($tableInput)) {
            $table = \App\Models\Table::with('floor')
                ->where('name', 'like', "%{$tableInput}%")
                ->orWhere('id', $tableInput)
                ->first();
        }

        $types = \App\Models\ServiceCall::TYPES;
        $callTypeLabel = $types[$callType]['label'] ?? 'Gọi nhân viên phục vụ';

        $user = $context['user'] ?? \Illuminate\Support\Facades\Auth::user();

        $serviceCall = \App\Models\ServiceCall::create([
            'table_id' => $table ? $table->id : null,
            'customer_name' => $user ? ($user->fullname ?? $user->name) : 'Khách qua Chatbot',
            'phone' => $user ? $user->phone : null,
            'call_type' => $callType,
            'call_type_label' => $callTypeLabel,
            'note' => $note,
            'status' => \App\Models\ServiceCall::STATUS_PENDING,
        ]);

        $tableName = $table ? ($table->floor ? "{$table->name} ({$table->floor->name})" : $table->name) : 'khu vực của bạn';

        return [
            'success' => true,
            'call_id' => $serviceCall->id,
            'table_name' => $tableName,
            'message' => "Dạ em đã phát chuông và gửi thông báo trực tiếp lên hệ thống cho nhân viên phục vụ rồi ạ! Nhân viên CIVILIZE sẽ đến {$tableName} hỗ trợ anh/chị ngay nhé ạ!"
        ];
    }

    /**
     * MAIN ORCHESTRATION PIPELINE
     */
    public function reply(string $message, array $context = []): array
    {
        $cleanMsg = trim(strip_tags($message));
        if ($cleanMsg === '') {
            return $this->formatResult([
                'reply' => 'Dạ em có thể giúp gì cho anh chị hôm nay ạ?'
            ]);
        }

        $sessionId = $context['session_id'] ?? session()->getId();
        $user = $context['user'] ?? null;

        // 1. Tải Short-Term Memory (10 lượt hội thoại gần nhất của session này)
        $recentMessages = collect([]);
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('chatbot_messages')) {
                $recentMessages = ChatbotMessage::where('session_id', $sessionId)
                    ->orderBy('id', 'asc')
                    ->limit(10)
                    ->get();
            }
        } catch (\Throwable $e) {
            Log::warning('Chatbot memory load failed: ' . $e->getMessage());
        }

        // 2. Tải Long-Term Memory (Hồ sơ ẩm thực của khách nếu có)
        $customerProfile = null;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('customer_ai_profiles')) {
                if (!empty($user['id'])) {
                    $customerProfile = CustomerAiProfile::where('user_id', $user['id'])->first();
                } elseif (!empty($user['phone'])) {
                    $customerProfile = CustomerAiProfile::where('phone', $user['phone'])->first();
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Customer profile load failed: ' . $e->getMessage());
        }

        // 3. Quyết định kênh tạo câu trả lời: Gemini 1.5 Flash Function Calling hoặc Native Fallback
        $apiKey = config('services.gemini.api_key') ?: env('GEMINI_API_KEY');
        $result = null;

        if (!empty($apiKey)) {
            $result = $this->generateWithGeminiAgent($cleanMsg, $recentMessages, $customerProfile, $context);
        }

        // Nếu không có API Key hoặc Gemini bị lỗi timeout/rate limit, dùng Native Deterministic Engine
        if (!$result) {
            $result = $this->generateWithNativeDeterministicEngine($cleanMsg, $recentMessages, $customerProfile, $context);
        }

        // 4. Lưu User Message & Assistant Message vào Short-term Memory (DB)
        $this->saveMessageToMemory($sessionId, $user['id'] ?? null, 'user', $cleanMsg);
        $this->saveMessageToMemory($sessionId, $user['id'] ?? null, 'assistant', $result['reply'] ?? '', $result['tool_calls'] ?? null, $result['tool_output'] ?? null);

        return $this->formatResult($result);
    }

    /**
     * LLM Agent Pipeline with Gemini 1.5 Flash Function Calling
     */
    protected function generateWithGeminiAgent(string $userMessage, $recentMessages, ?CustomerAiProfile $profile, array $context): ?array
    {
        $apiKey = config('services.gemini.api_key') ?: env('GEMINI_API_KEY');
        $model = config('services.gemini.model', 'gemini-1.5-pro');

        try {
            // Build System Instruction với Strict Business Guardrails
            $systemInstruction = "Bạn là Nhân viên hỗ trợ trực tuyến của Nhà hàng CIVILIZE (120 Phúc Hòa, Phúc Thọ, Hà Nội - Hotline: 0338 990 538).

NGUYÊN TẮC CỐT LÕI (BẮT BUỘC TUÂN THỦ TUYỆT ĐỐI):
1. TÔN TRỌNG DỮ LIỆU THỰC TẾ: Bạn KHÔNG được tự bịa ra món ăn, giá tiền hoặc tình trạng bàn. Hãy dùng công cụ 'search_menu_dishes' để lấy danh sách món và giá thật, dùng 'check_table_availability' để kiểm tra bàn trống.
2. CÂU HỎI MỞ / GỢI Ý MÓN / ĐẶC SẢN ('hôm nay có gì', 'hôm nay có món gì ngon', 'tư vấn món', 'đặc sản quán'):
   - Hãy đón tiếp khách niềm nở, chuyên nghiệp, giới thiệu 2-3 món signature nổi bật của nhà hàng (như Lẩu Thái, Lẩu bắp bò nhúng dấm, Sườn nướng BBQ) kèm mô tả hương vị hấp dẫn và giá tiền.
   - TUYỆT ĐỐI KHÔNG BAO GIỜ nói câu máy móc kiểu 'Không tìm thấy món đúng với từ khóa...'.
   - Luôn nhắc khách: Đặt bàn trước sẽ được GIẢM NGAY 10% trên tổng hóa đơn món ăn!
3. NẾU KHÁCH HỎI MÓN KHÔNG CÓ TRONG THỰC ĐƠN (ví dụ Pizza, Sushi, Bún bò Huế...): Lịch sự giải thích nhà hàng chuyên về ẩm thực Việt & Á (lẩu, nướng, mâm cơm gia đình, hải sản tươi sạch), sau đó dùng công cụ để gợi ý các món đặc sản có trong thực đơn.
4. QUY TRÌNH ĐẶT BÀN 2 BƯỚC:
   - Bước 1: Khi khách ngỏ ý đặt bàn -> Bắt buộc gọi 'check_table_availability'. Nếu còn bàn, tóm tắt thông tin và hỏi xin Họ Tên + SĐT để xác nhận giữ chỗ.
   - Bước 2: CHỈ ĐƯỢC gọi 'confirm_and_create_booking' khi khách ĐÃ ĐỒNG Ý xác nhận và cung cấp đầy đủ Tên, SĐT, Ngày, Giờ, Số khách. Tuyệt đối không tự ý đặt bàn khi khách mới chỉ hỏi bâng quơ.
5. TƯ VẤN KHẨU PHẦN & NGÂN SÁCH: Biết suy luận tính toán tổng tiền món ăn không vượt quá ngân sách khách yêu cầu. Nhắc khách đặt trước được GIẢM NGAY 10% tổng hóa đơn món ăn.
6. PHONG CÁCH GIAO TIẾP: Xưng em, gọi khách là 'anh/chị'. Ấm áp, lịch thiệp, chu đáo, tinh tế chuẩn tác phong nhà hàng ẩm thực Việt Nam.";

            if (!empty($context['user'])) {
                $u = $context['user'];
                $systemInstruction .= "\n\nTHÔNG TIN KHÁCH HÀNG ĐANG ĐĂNG NHẬP:";
                if (!empty($u['name'])) $systemInstruction .= "\n• Họ và tên: {$u['name']}";
                if (!empty($u['phone'])) $systemInstruction .= "\n• Số điện thoại: {$u['phone']}";
                if (!empty($u['email'])) $systemInstruction .= "\n• Email: {$u['email']}";
                $systemInstruction .= "\n(LƯU Ý: Khách đã đăng nhập. Bạn hãy chào hỏi thân thiết xưng hô theo tên khách (ví dụ: anh/chị {$u['name']}). Khi khách yêu cầu kiểm tra đơn hàng hoặc đặt bàn, bạn hãy chủ động sử dụng tên và số điện thoại này của khách để tra cứu hoặc hoàn tất đặt bàn mà không bắt khách phải gõ lại nếu khách không yêu cầu đổi).";
            }

            if ($profile) {
                $systemInstruction .= "\n\nHỒ SƠ KHÁCH HÀNG THÂN THIẾT (LONG-TERM MEMORY):";
                if ($profile->customer_name) $systemInstruction .= "\n• Tên khách: {$profile->customer_name}";
                if ($profile->taste_preferences) $systemInstruction .= "\n• Sở thích ẩm thực: " . implode(', ', $profile->taste_preferences);
                if ($profile->dietary_restrictions) $systemInstruction .= "\n• Kiêng cữ / Dị ứng: " . implode(', ', $profile->dietary_restrictions);
                if ($profile->favorite_seating) $systemInstruction .= "\n• Vị trí ngồi yêu thích: {$profile->favorite_seating}";
                if ($profile->total_visits) $systemInstruction .= "\n• Số lần đã ghé quán: {$profile->total_visits} lần";
            }

            // Xây dựng lịch sử hội thoại nhiều lượt (Multi-turn Context)
            $contents = [];
            foreach ($recentMessages as $msg) {
                $role = ($msg->role === 'user') ? 'user' : 'model';
                $contents[] = [
                    'role' => $role,
                    'parts' => [
                        ['text' => $msg->content ?? '']
                    ]
                ];
            }
            // Append tin nhắn hiện tại của khách
            $contents[] = [
                'role' => 'user',
                'parts' => [
                    ['text' => $userMessage]
                ]
            ];

            // Request payload gửi Gemini
            $toolsPayload = [
                [
                    'functionDeclarations' => $this->getToolDeclarations()
                ]
            ];

            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
            $response = Http::withHeaders([
                'Content-Type' => 'application/json'
            ])->timeout(18)->post($url, [
                'systemInstruction' => [
                    'parts' => [['text' => $systemInstruction]]
                ],
                'contents' => $contents,
                'tools' => $toolsPayload,
                'generationConfig' => [
                    'temperature' => 0.3,
                    'maxOutputTokens' => 350
                ]
            ]);

            if (!$response->successful()) {
                Log::warning('Gemini API call returned non-200: ' . $response->body());
                return null;
            }

            $data = $response->json();
            $candidate = $data['candidates'][0] ?? null;
            if (!$candidate) return null;

            $part = $candidate['content']['parts'][0] ?? null;

            // KIỂM TRA XEM GEMINI CÓ QUYẾT ĐỊNH GỌI TOOL KHÔNG
            if (!empty($part['functionCall'])) {
                $call = $part['functionCall'];
                $toolName = $call['name'];
                $toolArgs = $call['args'] ?? [];

                // Thực thi Tool nội bộ trong Laravel
                $toolResult = $this->executeTool($toolName, $toolArgs, $context);

                // Gửi functionResponse ngược lại cho Gemini để sinh câu trả lời tự nhiên
                // Đảm bảo functionCall.args là stdClass nếu rỗng để json_encode thành {} thay vì []
                $modelPart = $part;
                if (isset($modelPart['functionCall']['args']) && empty($modelPart['functionCall']['args'])) {
                    $modelPart['functionCall']['args'] = new \stdClass();
                }

                $contents[] = [
                    'role' => 'model',
                    'parts' => [$modelPart]
                ];
                $contents[] = [
                    'role' => 'user',
                    'parts' => [
                        [
                            'functionResponse' => [
                                'name' => $toolName,
                                'response' => $toolResult
                            ]
                        ]
                    ]
                ];

                $secondResponse = Http::withHeaders([
                    'Content-Type' => 'application/json'
                ])->timeout(18)->post($url, [
                    'systemInstruction' => [
                        'parts' => [['text' => $systemInstruction]]
                    ],
                    'contents' => $contents,
                    'tools' => $toolsPayload,
                    'generationConfig' => [
                        'temperature' => 0.4,
                        'maxOutputTokens' => 600
                    ]
                ]);

                $products = [];
                if ($toolName === 'search_menu_dishes' && !empty($toolResult['dishes'])) {
                    $products = $this->formatDishesForCard($toolResult['dishes']);
                }

                if ($secondResponse->successful()) {
                    $secondData = $secondResponse->json();
                    $finalParts = $secondData['candidates'][0]['content']['parts'] ?? [];
                    $finalText = '';
                    foreach ($finalParts as $fp) {
                        if (!empty($fp['text'])) {
                            $finalText .= $fp['text'];
                        }
                    }

                    if (!empty(trim($finalText))) {
                        // Sắp xếp ưu tiên các món mà Gemini thực sự đã nhắc đến trong lời nhắn
                        if ($toolName === 'search_menu_dishes' && !empty($toolResult['dishes'])) {
                            $allDishes = $toolResult['dishes'];
                            $lowerFinal = mb_strtolower($finalText, 'UTF-8');
                            usort($allDishes, function($a, $b) use ($lowerFinal) {
                                $aMentioned = str_contains($lowerFinal, mb_strtolower($a['name'], 'UTF-8'));
                                $bMentioned = str_contains($lowerFinal, mb_strtolower($b['name'], 'UTF-8'));
                                if ($aMentioned === $bMentioned) return 0;
                                return $aMentioned ? -1 : 1;
                            });
                            $products = $this->formatDishesForCard($allDishes);
                        }

                        return [
                            'reply' => trim($finalText),
                            'products' => $products,
                            'tool_calls' => [$call],
                            'tool_output' => $toolResult,
                            'quick_replies' => $this->generateContextualQuickReplies($toolName, $toolResult)
                        ];
                    }
                } else {
                    Log::warning('Gemini 2nd Turn Error: ' . $secondResponse->body());
                }

                // Graceful fallback nếu 2nd turn không sinh được text
                $fallbackReply = "Dạ em đã kiểm tra thông tin cho mình rồi ạ!";
                if ($toolName === 'search_menu_dishes') {
                    $fallbackReply = "Dạ nhà hàng CIVILIZE bên em có rất nhiều món ăn đặc sắc và các món lẩu, nướng thơm ngon được đông đảo thực khách yêu thích. Em xin gửi anh/chị tham khảo danh sách bên dưới nhé ạ! (Đặc biệt đặt bàn trước sẽ được GIẢM NGAY 10% tổng hóa đơn đó ạ!)";
                } elseif ($toolName === 'check_table_availability') {
                    $fallbackReply = ($toolResult['message'] ?? 'Dạ nhà hàng hiện vẫn còn bàn phục vụ quý khách ạ.') . "\n\nĐể em giữ vị trí bàn đẹp nhất và áp dụng ưu đãi GIẢM NGAY 10% cho đoàn mình, anh chị cho em xin Họ tên và Số điện thoại nhé ạ!";
                }

                return [
                    'reply' => $fallbackReply,
                    'products' => $products,
                    'tool_calls' => [$call],
                    'tool_output' => $toolResult,
                    'quick_replies' => $this->generateContextualQuickReplies($toolName, $toolResult)
                ];
            }

            // Nếu Gemini trả lời trực tiếp mà không cần tool
            $directParts = $candidate['content']['parts'] ?? [];
            $replyText = '';
            foreach ($directParts as $dp) {
                if (!empty($dp['text'])) {
                    $replyText .= $dp['text'];
                }
            }
            if (!empty($replyText)) {
                return [
                    'reply' => trim($replyText),
                    'products' => [],
                    'quick_replies' => [
                        ['title' => 'Gợi ý món ngon hôm nay', 'payload' => 'Gợi ý món ngon hôm nay'],
                        ['title' => 'Đặt bàn trước nhận 10%', 'payload' => 'Tôi muốn đặt bàn'],
                        ['title' => 'Địa chỉ & Giờ mở cửa', 'payload' => 'Địa chỉ quán ở đâu?']
                    ]
                ];
            }

        } catch (\Exception $e) {
            Log::error('Gemini Agent Error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Native Deterministic Engine (Offline / Zero-latency Fallback)
     * Still respects ALL 6 Intelligent Pillars using short-term memory & actual DB queries
     */
    protected function generateWithNativeDeterministicEngine(string $userMsg, $recentMessages, ?CustomerAiProfile $profile, array $context): array
    {
        $lower = mb_strtolower($userMsg, 'UTF-8');
        $normalized = $this->removeAccents($lower);

        // Đọc lịch sử xem lượt trước khách có đang đặt bàn hay hỏi món gì không
        $lastUserMsg = '';
        $lastBotMsg = '';
        if ($recentMessages && $recentMessages->count() >= 1) {
            foreach ($recentMessages as $rm) {
                if ($rm->role === 'user') {
                    $lastUserMsg = mb_strtolower($rm->content ?? '', 'UTF-8');
                } elseif ($rm->role === 'assistant') {
                    $lastBotMsg = mb_strtolower($rm->content ?? '', 'UTF-8');
                }
            }
        }

        // Tình huống 1: Hỏi món ăn không có trong thực đơn (Ví dụ: Pizza, Sushi, bún chả)
        if ($this->matchAny($lower, ['pizza', 'sushi', 'bún bò huế', 'bún chả', 'phở bò', 'bánh mì pate', 'hamburger', 'mì ý', 'steak bò úc'])) {
            $fallbackDishes = Product::where('status', 1)->whereIn('category_id', [8, 12, 1])->limit(3)->get();
            return [
                'reply' => "Dạ CIVILIZE là nhà hàng chuyên ẩm thực Việt & Á truyền thống với các món lẩu ninh ngọt tự nhiên, đồ nướng than hoa đậm vị và mâm cơm gia đình ấm cúng, nên hiện tại nhà hàng chưa phục vụ món này anh chị nha!\n\nEm xin phép gợi ý một số món đặc sản cực kỳ được ưa chuộng tại nhà hàng dưới đây để mình thưởng thức nhé ạ:",
                'products' => $this->formatDishesForCard($this->formatDishesForTool($fallbackDishes)),
                'quick_replies' => [
                    ['title' => 'Xem thực đơn lẩu', 'payload' => 'Cho tôi xem thực đơn lẩu'],
                    ['title' => 'Xem món nướng', 'payload' => 'Có món nướng gì ngon?'],
                    ['title' => 'Đặt bàn nhận ưu đãi 10%', 'payload' => 'Tôi muốn đặt bàn']
                ]
            ];
        }

        // Tình huống 2: Khách xác nhận đặt bàn hoặc cung cấp thông tin (SĐT + Tên)
        preg_match('/(0[3|5|7|8|9][0-9]{8})/u', $userMsg, $phoneMatch);
        $extractedPhone = $phoneMatch[1] ?? null;

        // Nếu lượt trước bot đang hỏi SĐT/Tên để chốt bàn, hoặc câu hiện tại có đủ Tên + SĐT + Số người
        $isBookingConfirm = $extractedPhone || (str_contains($lastBotMsg, 'xác nhận giữ bàn') && $this->matchAny($lower, ['ok', 'đồng ý', 'chốt nhé', 'đặt đi', 'xác nhận']));

        if ($isBookingConfirm) {
            $people = 4;
            if (preg_match('/([1-9][0-9]?)\s*(?:người|khách|bàn|pax)/iu', $userMsg . ' ' . $lastBotMsg, $m)) {
                $people = (int)$m[1];
            }

            $date = Carbon::today()->toDateString();
            if (str_contains($lower, 'mai') || str_contains($lastBotMsg, 'ngày mai')) {
                $date = Carbon::tomorrow()->toDateString();
            }

            $time = '19:00';
            if (preg_match('/([0-2]?[0-9])\s*(?:h|:|giờ)([0-5][0-9])?/iu', $userMsg . ' ' . $lastBotMsg, $tm)) {
                $h = str_pad($tm[1], 2, '0', STR_PAD_LEFT);
                $min = !empty($tm[2]) ? str_pad($tm[2], 2, '0', STR_PAD_LEFT) : '00';
                $time = "{$h}:{$min}";
            }

            $customerName = $context['user']['name'] ?? 'Quý khách';
            if (preg_match('/(?:tên|anh|chị|mình là)\s+([a-zA-ZÀ-ỹ\s]{2,20})/iu', $userMsg, $nameMatch)) {
                $customerName = trim($nameMatch[1]);
            }

            if ($extractedPhone) {
                // Tạo đơn đặt bàn thật
                $bookingResult = $this->toolConfirmAndCreateBooking([
                    'name' => $customerName,
                    'phone' => $extractedPhone,
                    'number_of_people' => $people,
                    'booking_date' => $date,
                    'booking_time' => $time,
                    'note' => 'Đặt bàn qua Chatbot AI CIVILIZE'
                ], $context);

                return [
                    'reply' => "Dạ em đã hoàn tất đặt bàn cho mình rồi ạ!\n\n" .
                               "• Mã đặt bàn: #{$bookingResult['booking_id']}\n" .
                               "• Khách hàng: {$customerName} ({$extractedPhone})\n" .
                               "• Số lượng: {$people} khách\n" .
                               "• Thời gian: {$time} ngày " . Carbon::parse($date)->format('d/m/Y') . "\n" .
                               "• Ưu đãi: GIẢM NGAY 10% trên tổng hóa đơn món ăn.\n\n" .
                               "Nhà hàng sẽ gọi điện thoại xác nhận chu đáo trước giờ hẹn. Rất hân hạnh được đón tiếp anh chị ạ!",
                    'products' => [],
                    'quick_replies' => [
                        ['title' => 'Xem thực đơn trước', 'payload' => 'Xem thực đơn'],
                        ['title' => 'Chỉ đường tới quán', 'payload' => 'Địa chỉ của quán'],
                        ['title' => 'Quy định mang rượu', 'payload' => 'Mang rượu ngoài vào có tính phí không?']
                    ]
                ];
            } else {
                // Bước 1: Hỏi xin SĐT để xác nhận
                $availResult = $this->toolCheckTableAvailability([
                    'number_of_people' => $people,
                    'booking_date' => $date,
                    'booking_time' => $time
                ]);

                return [
                    'reply' => "{$availResult['message']}\n\n" .
                               "Để em hoàn tất giữ bàn đẹp nhất cho đoàn mình, anh chị vui lòng để lại Tên và Số điện thoại liên hệ nhé ạ!",
                    'products' => [],
                    'quick_replies' => [
                        ['title' => 'Hủy đặt bàn', 'payload' => 'Thôi không đặt nữa'],
                        ['title' => 'Tư vấn món trước', 'payload' => 'Gợi ý món ngon trước']
                    ]
                ];
            }
        }

        // Tình huống 3: Tư vấn món theo ngân sách, sở thích hoặc khẩu phần (Reasoning)
        // Ưu tiên chạy trước nếu khách rõ ràng đang hỏi chọn món/tư vấn set ăn/ngân sách
        preg_match('/(?:ngân sách|tầm|khoảng|giá)\s*([0-9]+)\s*(?:k|nghìn|ngan|tr|triệu|đ)/iu', $userMsg, $bMatch);
        $hasBudget = !empty($bMatch[1]);
        $hasSpecificFoodConsult = $this->matchAny($lower, ['tư vấn', 'tu van', 'set', 'ăn gì', 'an gi', 'món gì', 'mon gi', 'món nào', 'gợi ý món', 'thực đơn lẩu', 'thực đơn nướng', 'hôm nay có gì', 'hôm nay có món', 'đặc sản', 'bestseller']) 
            || ($this->matchAny($lower, ['lẩu', 'nướng', 'món']) && ($hasBudget || $this->matchAny($lower, ['thanh mát', 'không cay', 'cho trẻ em', 'ăn nhậu'])));

        if ($hasSpecificFoodConsult || $hasBudget) {
            $keyword = '';
            if (str_contains($lower, 'lẩu')) $keyword = 'lẩu';
            elseif (str_contains($lower, 'nướng')) $keyword = 'nướng';
            elseif (str_contains($lower, 'bò')) $keyword = 'bò';
            elseif (str_contains($lower, 'gà')) $keyword = 'gà';
            elseif (str_contains($lower, 'cá')) $keyword = 'cá';
            elseif (str_contains($lower, 'tôm')) $keyword = 'tôm';
            elseif (str_contains($lower, 'khai vị')) $keyword = 'khai vị';
            elseif (str_contains($lower, 'uống') || str_contains($lower, 'nước') || str_contains($lower, 'cafe')) $keyword = 'đồ uống';

            $dishesResult = $this->toolSearchMenuDishes(['keyword' => $keyword]);
            $dishes = $dishesResult['dishes'] ?? [];

            $people = null;
            if (preg_match('/([1-9][0-9]?)\s*(?:người|khách|pax)/iu', $userMsg . ' ' . $lastUserMsg, $pm)) {
                $people = (int)$pm[1];
            }

            if ($people) {
                $intro = "Dạ với đoàn {$people} khách, em gợi ý mâm cỗ ẩm thực hài hòa hương vị, kết hợp giữa món chính đậm đà và các món khai vị thanh mát tại CIVILIZE ạ:\n\n";
                if (!empty($bMatch[1])) {
                    $intro = "Dạ với ngân sách tầm {$bMatch[0]} cho đoàn {$people} người, em xin tư vấn set thực đơn no nê, cân đối trọn vị và hoàn toàn không vượt quá ngân sách của mình ạ:\n\n";
                }
            } else {
                $intro = "Dạ nhà hàng CIVILIZE bên em hôm nay có rất nhiều món ăn đặc sắc và các món lẩu, nướng thơm ngon được đông đảo thực khách yêu thích. Em xin gợi ý một số món tiêu biểu để anh/chị tham khảo nhé ạ:\n\n";
            }

            return [
                'reply' => $intro . "Anh chị có thể tham khảo danh sách món ăn thực tế bên dưới hoặc bấm đặt bàn trước để được GIẢM NGAY 10% tổng hóa đơn nhé ạ!",
                'products' => $this->formatDishesForCard($dishes),
                'quick_replies' => [
                    ['title' => 'Đặt bàn trước giảm 10%', 'payload' => 'Tôi muốn đặt bàn'],
                    ['title' => 'Đổi sang món lẩu', 'payload' => 'Cho tôi xem món lẩu'],
                    ['title' => 'Địa chỉ nhà hàng', 'payload' => 'Địa chỉ của quán']
                ]
            ];
        }

        // Tình huống 4: Hỏi về bàn / Đặt bàn / Kiểm tra chỗ ngồi
        $hasBookingIntent = $this->matchAny($lower, ['đặt bàn', 'dat ban', 'còn bàn', 'book bàn', 'giữ bàn', 'phòng vip', 'còn chỗ', 'con cho', 'hết bàn'])
            || (str_contains($lower, 'bàn') && preg_match('/(?:[0-9]+|người|khách|tối|trưa|mai)/iu', $userMsg))
            || ($this->matchAny($lower, ['người', 'khách']) && preg_match('/(?:[0-2]?[0-9]\s*(?:h|:|giờ)|tối nay|trưa nay|ngày mai)/iu', $userMsg));

        if ($hasBookingIntent) {
            $prevPeople = 0;
            if (preg_match('/([1-9][0-9]?)\s*(?:người|khách|pax)/iu', $lastUserMsg, $lpm)) {
                $prevPeople = (int)$lpm[1];
            }

            $people = 4;
            if (preg_match('/([1-9][0-9]?)\s*(?:người|khách|pax)/iu', $userMsg, $pm)) {
                $currentPeople = (int)$pm[1];
                if ($this->matchAny($lower, ['thêm', 'nữa', 'cộng thêm', 'gia đình anh', 'bạn anh']) && $prevPeople > 0) {
                    $people = $prevPeople + $currentPeople;
                } else {
                    $people = $currentPeople;
                }
            } elseif ($prevPeople > 0) {
                $people = $prevPeople;
            }

            $date = Carbon::today()->toDateString();
            if (str_contains($lower, 'mai') || str_contains($lastUserMsg, 'mai')) {
                $date = Carbon::tomorrow()->toDateString();
            }

            $time = '19:00';
            if (preg_match('/([0-2]?[0-9])\s*(?:h|:|giờ)([0-5][0-9])?/iu', $userMsg . ' ' . $lastUserMsg, $tm)) {
                $rawHour = (int)$tm[1];
                if (($this->matchAny($lower, ['tối', 'chiều']) || str_contains($lastUserMsg, 'tối')) && $rawHour >= 1 && $rawHour <= 11) {
                    $rawHour += 12;
                }
                $h = str_pad($rawHour, 2, '0', STR_PAD_LEFT);
                $min = !empty($tm[2]) ? str_pad($tm[2], 2, '0', STR_PAD_LEFT) : '00';
                $time = "{$h}:{$min}";
            }

            $avail = $this->toolCheckTableAvailability([
                'number_of_people' => $people,
                'booking_date' => $date,
                'booking_time' => $time
            ]);

            return [
                'reply' => "Dạ em kiểm tra bàn cho mình lúc {$time} ngày " . Carbon::parse($date)->format('d/m/Y') . " rồi ạ:\n\n" .
                           "• Tình trạng: {$avail['message']}\n\n" .
                           "Để em hoàn tất giữ bàn vị trí đẹp nhất và kích hoạt ưu đãi GIẢM NGAY 10% cho đoàn mình, anh chị cho em xin Họ tên và Số điện thoại liên hệ nhé ạ!",
                'products' => [],
                'quick_replies' => [
                    ['title' => 'Tư vấn món trước', 'payload' => 'Gợi ý món ngon trước'],
                    ['title' => 'Xem thực đơn', 'payload' => 'Xem thực đơn'],
                    ['title' => 'Quy định mang rượu', 'payload' => 'Mang rượu ngoài có tính phí không?']
                ]
            ];
        }

        // Tình huống 5: Tra cứu chính sách nhà hàng (Bãi xe, Rượu ngoài, Giờ mở cửa, Phòng VIP)
        foreach ($this->knowledgeBase as $key => $chunk) {
            foreach ($chunk['keywords'] as $kw) {
                if (str_contains($lower, $kw) || str_contains($normalized, $this->removeAccents($kw))) {
                    return [
                        'reply' => "Dạ về {$chunk['title']} tại CIVILIZE Restaurant:\n\n{$chunk['content']}",
                        'products' => [],
                        'quick_replies' => [
                            ['title' => 'Đặt bàn trước giảm 10%', 'payload' => 'Tôi muốn đặt bàn'],
                            ['title' => 'Gợi ý món ngon', 'payload' => 'Gợi ý món ngon hôm nay'],
                            ['title' => 'Hotline nhà hàng', 'payload' => 'Hotline của quán']
                        ]
                    ];
                }
            }
        }

        // Mặc định: Lời chào thân thiện và gợi ý tác vụ
        return [
            'reply' => "Dạ em chào anh/chị ạ! Em là nhân viên tư vấn của CIVILIZE Restaurant. Em luôn sẵn sàng hỗ trợ gợi ý món ngon hợp khẩu vị, kiểm tra bàn trống, đặt bàn giữ chỗ nhận ưu đãi 10% hoặc giải đáp các thắc mắc của quán. Hôm nay em có thể hỗ trợ gì cho mình ạ?",
            'products' => [],
            'quick_replies' => [
                ['title' => 'Gợi ý món ngon hôm nay', 'payload' => 'Gợi ý món ngon hôm nay'],
                ['title' => 'Đặt bàn nhận ưu đãi 10%', 'payload' => 'Tôi muốn đặt bàn'],
                ['title' => 'Địa chỉ & Giờ mở cửa', 'payload' => 'Địa chỉ quán ở đâu?']
            ]
        ];
    }

    /**
     * Helpers: Short-term memory saver
     */
    protected function saveMessageToMemory(string $sessionId, ?int $userId, string $role, string $content, ?array $toolCalls = null, ?array $toolOutput = null): void
    {
        try {
            ChatbotMessage::create([
                'session_id' => $sessionId,
                'user_id' => $userId,
                'role' => $role,
                'content' => $content,
                'tool_calls' => $toolCalls,
                'tool_output' => $toolOutput
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to save chatbot message memory: ' . $e->getMessage());
        }
    }

    /**
     * Helpers: Long-term profile updater
     */
    protected function updateCustomerProfile(string $phone, array $data): void
    {
        try {
            $profile = CustomerAiProfile::firstOrNew(['phone' => $phone]);
            if (!empty($data['customer_name'])) $profile->customer_name = $data['customer_name'];
            if (!empty($data['user_id'])) $profile->user_id = $data['user_id'];
            if (!empty($data['total_visits_increment'])) $profile->total_visits = ($profile->total_visits ?? 0) + 1;
            $profile->save();
        } catch (\Exception $e) {
            Log::warning('Failed to update customer profile: ' . $e->getMessage());
        }
    }

    /**
     * Format dishes from Model to Tool array
     */
    protected function formatDishesForTool($items): array
    {
        $result = [];
        foreach ($items as $item) {
            $price = (!empty($item->price_sale) && $item->price_sale > 0 && $item->price_sale < $item->price_buy)
                ? $item->price_sale
                : $item->price_buy;

            $imageFile = $item->image ?? $item->thumbnail ?? '';

            $result[] = [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'price' => $price,
                'price_formatted' => number_format($price, 0, ',', '.') . '₫',
                'description' => $item->description ?? '',
                'image' => $imageFile,
                'thumbnail' => $imageFile
            ];
        }
        return $result;
    }

    /**
     * Format dishes from Tool array or Model collection to UI Cards
     */
    protected function formatDishesForCard($dishes): array
    {
        $cards = [];
        $list = is_array($dishes) ? $dishes : ($dishes ? $dishes->all() : []);
        foreach (array_slice($list, 0, 4) as $d) {
            $id = is_object($d) ? $d->id : ($d['id'] ?? null);
            $name = is_object($d) ? $d->name : ($d['name'] ?? '');
            $slug = is_object($d) ? $d->slug : ($d['slug'] ?? 'mon-an');
            $imageFile = is_object($d) ? ($d->image ?? $d->thumbnail ?? '') : ($d['image'] ?? $d['thumbnail'] ?? '');

            $priceRaw = is_object($d)
                ? ((!empty($d->price_sale) && $d->price_sale > 0 && $d->price_sale < $d->price_buy) ? $d->price_sale : $d->price_buy)
                : ($d['price'] ?? 0);
            $priceFormatted = is_object($d)
                ? (number_format($priceRaw, 0, ',', '.') . '₫')
                : ($d['price_formatted'] ?? (number_format($priceRaw, 0, ',', '.') . '₫'));

            $imgUrl = asset('images/default-product.png');
            if (!empty($imageFile)) {
                if (str_starts_with($imageFile, 'http://') || str_starts_with($imageFile, 'https://')) {
                    $imgUrl = $imageFile;
                } else {
                    $imgUrl = asset('images/product/' . $imageFile);
                }
            }

            $cards[] = [
                'id' => $id,
                'name' => $name,
                'price' => $priceFormatted,
                'image' => $imgUrl,
                'url' => route('site.product.detail', ['slug' => $slug])
            ];
        }
        return $cards;
    }

    /**
     * Generate context-aware quick replies based on tool executed
     */
    protected function generateContextualQuickReplies(string $toolName, array $toolOutput): array
    {
        switch ($toolName) {
            case 'search_menu_dishes':
                return [
                    ['title' => 'Đặt bàn trước giảm 10%', 'payload' => 'Tôi muốn đặt bàn'],
                    ['title' => 'Xem thực đơn lẩu', 'payload' => 'Cho tôi xem món lẩu'],
                    ['title' => 'Địa chỉ của quán', 'payload' => 'Địa chỉ quán ở đâu?']
                ];
            case 'check_table_availability':
                return [
                    ['title' => 'Xác nhận đặt bàn luôn', 'payload' => 'Tôi đồng ý đặt bàn'],
                    ['title' => 'Tư vấn món trước', 'payload' => 'Gợi ý món ngon trước']
                ];
            case 'confirm_and_create_booking':
                return [
                    ['title' => 'Xem thực đơn', 'payload' => 'Xem thực đơn món ăn'],
                    ['title' => 'Chỉ đường Google Maps', 'payload' => 'Chỉ đường tới quán']
                ];
            default:
                return [
                    ['title' => 'Gợi ý món ngon hôm nay', 'payload' => 'Gợi ý món ngon hôm nay'],
                    ['title' => 'Đặt bàn trước nhận 10%', 'payload' => 'Tôi muốn đặt bàn'],
                    ['title' => 'Địa chỉ & Giờ mở cửa', 'payload' => 'Địa chỉ quán ở đâu?']
                ];
        }
    }

    /**
     * Format final result array
     */
    protected function formatResult(array $result): array
    {
        return [
            'reply' => $result['reply'] ?? '',
            'products' => $result['products'] ?? [],
            'quick_replies' => $result['quick_replies'] ?? [],
            'tool_calls' => $result['tool_calls'] ?? null,
            'tool_output' => $result['tool_output'] ?? null
        ];
    }

    protected function matchAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $n) {
            if (str_contains($haystack, mb_strtolower($n, 'UTF-8'))) {
                return true;
            }
        }
        return false;
    }

    public function removeAccents(string $str): string
    {
        $accents = [
            'a' => ['à','á','ạ','ả','ã','â','ầ','ấ','ậ','ẩ','ẫ','ă','ằ','ắ','ặ','ẳ','ẵ'],
            'e' => ['è','é','ẹ','ẻ','ẽ','ê','ề','ế','ệ','ể','ễ'],
            'i' => ['ì','í','ị','ỉ','ĩ'],
            'o' => ['ò','ó','ọ','ỏ','õ','ô','ồ','ố','ộ','ổ','ỗ','ơ','ờ','ớ','ợ','ở','ỡ'],
            'u' => ['ù','ú','ụ','ủ','ũ','ư','ừ','ứ','ự','ử','ữ'],
            'y' => ['ỳ','ý','ỵ','ỷ','ỹ'],
            'd' => ['đ'],
            'A' => ['À','Á','Ạ','Ả','Ã','Â','Ầ','Ấ','Ậ','Ẩ','Ẫ','Ă','Ằ','Ắ','Ặ','Ẳ','Ẵ'],
            'E' => ['È','É','Ẹ','Ẻ','Ẽ','Ê','Ề','Ế','Ệ','Ể','Ễ'],
            'I' => ['Ì','Í','Ị','Ỉ','Ĩ'],
            'O' => ['Ò','Ó','Ọ','Ỏ','Õ','Ô','Ồ','Ố','Ộ','Ổ','Ỗ','Ơ','Ờ','Ớ','Ợ','Ở','Ỡ'],
            'U' => ['Ù','Ú','Ụ','Ủ','Ũ','Ư','Ừ','Ứ','Ự','Ử','Ữ'],
            'Y' => ['Ỳ','Ý','Ỵ','Ỷ','Ỹ'],
            'D' => ['Đ']
        ];
        foreach ($accents as $non => $list) {
            $str = str_replace($list, $non, $str);
        }
        return mb_strtolower($str, 'UTF-8');
    }
}
