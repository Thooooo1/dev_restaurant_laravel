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
     * Runtime settings loaded dynamically
     */
    protected array $settings = [];

    /**
     * Unstructured Knowledge Base Chunks (Policies, Amenities, Facilities)
     */
    protected array $knowledgeBase = [
        'general_info' => [
            'title' => 'Tổng quan nhà hàng',
            'keywords' => ['nhà hàng civilize', 'về civilize', 'giới thiệu', 'thương hiệu', 'phong cách ẩm thực', 'sứ mệnh'],
            'content' => 'CIVILIZE Restaurant là nhà hàng ẩm thực Việt & Á đương đại tại Cụm 1, TT. Phúc Thọ, Huyện Phúc Thọ, TP. Hà Nội. Tôn chỉ hoạt động luôn đặt chữ Tâm và chữ Tín lên hàng đầu, mang đến không gian ấm cúng, sang trọng cùng những món ăn tinh hoa được chế biến tỉ mỉ từ nguyên liệu tươi sạch mỗi ngày.'
        ],
        'location_and_hours' => [
            'title' => 'Địa chỉ & Giờ mở cửa',
            'keywords' => ['địa chỉ', 'ở đâu', 'chỗ nào', 'vị trí', 'đường đi', 'tìm quán', 'giờ mở cửa', 'mấy giờ', 'mở cửa', 'đóng cửa', 'thời gian phục vụ'],
            'content' => 'Nhà hàng CIVILIZE mở cửa phục vụ: 09:00 - 23:00 tất cả các ngày trong tuần (kể cả Thứ 7, Chủ Nhật và các ngày Lễ, Tết). Địa chỉ chính xác tại: Cụm 1, TT. Phúc Thọ, Huyện Phúc Thọ, TP. Hà Nội.'
        ],
        'hotline_and_contact' => [
            'title' => 'Hotline & Liên hệ',
            'keywords' => ['hotline', 'số điện thoại', 'sđt', 'liên hệ', 'gọi điện', 'điện thoại', 'chăm sóc khách hàng'],
            'content' => 'Hotline hỗ trợ và đặt bàn của CIVILIZE Restaurant là: 0338 990 538. Quý khách có thể gọi trực tiếp bất cứ lúc nào để được hỗ trợ vị trí đẹp hoặc giải đáp thắc mắc.'
        ],
        'parking_facilities' => [
            'title' => 'Bãi đỗ xe máy & Ô tô',
            'keywords' => ['đỗ xe', 'gửi xe', 'bãi xe', 'đậu xe', 'chỗ để xe', 'ô tô', 'xe máy', 'chỗ đỗ ô tô', 'xe bán tải', 'xe du lịch'],
            'content' => 'CIVILIZE Restaurant có bãi đỗ xe riêng biệt siêu rộng rãi cho cả xe máy và ô tô (kể cả xe bán tải, xe du lịch 16-45 chỗ). Bãi xe hoàn toàn MIỄN PHÍ, có đội ngũ bảo vệ trực an ninh trông giữ 24/24.'
        ],
        'reservation_policy' => [
            'title' => 'Chính sách đặt bàn & Ưu đãi',
            'keywords' => ['ưu đãi đặt bàn', 'giảm giá đặt bàn', 'khuyến mãi', 'chiết khấu đặt bàn', 'giữ bàn bao lâu'],
            'content' => 'Quý khách có thể đặt bàn trực tuyến dễ dàng qua website hoặc chatbot. Nhà hàng sẽ giữ bàn tối đa 15-30 phút so với giờ hẹn. Đặc biệt, khi dùng bữa tại nhà hàng và gọi món qua mã QR tại bàn, quý khách sẽ được giảm ngay 10% trên tổng hóa đơn món ăn.'
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
            'content' => 'Nhà hàng phục vụ giao món tận nơi siêu tốc trong vòng 30–45 phút với hộp giữ nhiệt chuyên dụng. MIỄN PHÍ vận chuyển toàn bộ cho đơn hàng từ 500.000₫ trong bán kính 15km.'
        ],
        'payment_methods' => [
            'title' => 'Phương thức thanh toán',
            'keywords' => ['thanh toán', 'chuyển khoản', 'tiền mặt', 'cod', 'quẹt thẻ', 'vietqr', 'hóa đơn vat'],
            'content' => 'Quý khách có thể thanh toán bằng Tiền mặt khi nhận hàng (COD), quẹt thẻ ngân hàng tại quầy (Visa, Mastercard, ATM) hoặc quét mã VietQR tự động. Nhà hàng hỗ trợ xuất hóa đơn VAT điện tử.'
        ],
    ];

    public function __construct()
    {
        try {
            if (class_exists(\App\Http\Controllers\backend\SettingController::class)) {
                $this->settings = (new \App\Http\Controllers\backend\SettingController())->getSettings();
            }
        } catch (\Throwable $e) {
            $this->settings = [];
        }
        $this->syncDynamicKnowledge();
    }

    public function getSetting(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    protected function syncDynamicKnowledge(): void
    {
        $hotline = $this->getSetting('hotline', '0338 990 538');
        $address = $this->getSetting('address', 'Cụm 1, TT. Phúc Thọ, Huyện Phúc Thọ, TP. Hà Nội');
        $hours = $this->getSetting('opening_hours', '09:00 - 23:00 tất cả các ngày trong tuần');
        $freeShipThreshold = number_format((float)$this->getSetting('shipping_free_threshold', 500000), 0, ',', '.') . '₫';
        $maxKm = $this->getSetting('max_delivery_distance_km', 15);

        $this->knowledgeBase['general_info']['content'] = "CIVILIZE Restaurant là nhà hàng ẩm thực Việt & Á đương đại tại {$address}. Tôn chỉ hoạt động luôn đặt chữ Tâm và chữ Tín lên hàng đầu, mang đến không gian ấm cúng, sang trọng cùng những món ăn tinh hoa được chế biến tỉ mỉ từ nguyên liệu tươi sạch mỗi ngày.";
        $this->knowledgeBase['location_and_hours']['content'] = "Nhà hàng CIVILIZE mở cửa phục vụ: {$hours}. Địa chỉ chính xác tại: {$address}.";
        $this->knowledgeBase['hotline_and_contact']['content'] = "Hotline hỗ trợ và đặt bàn của CIVILIZE Restaurant là: {$hotline}. Quý khách có thể gọi trực tiếp bất cứ lúc nào để được hỗ trợ vị trí đẹp và giải đáp thắc mắc.";
        $this->knowledgeBase['reservation_policy']['content'] = "Quý khách có thể đặt bàn trực tuyến dễ dàng qua website hoặc chatbot. Nhà hàng sẽ giữ bàn tối đa 15-30 phút so với giờ hẹn. Đặc biệt, khi dùng bữa tại nhà hàng và gọi món qua mã QR tại bàn, quý khách sẽ được giảm ngay 10% trên tổng hóa đơn món ăn.";
        $this->knowledgeBase['delivery_and_shipping']['content'] = "Nhà hàng phục vụ giao món tận nơi siêu tốc trong vòng 30–45 phút với hộp giữ nhiệt chuyên dụng. MIỄN PHÍ vận chuyển toàn bộ cho đơn hàng từ {$freeShipThreshold} trong bán kính {$maxKm}km.";
    }

    /**
     * Mask customer name for privacy (e.g. Nguyễn Văn An -> Ng*** V** An)
     */
    protected function maskName(?string $name): string
    {
        if (empty($name)) return 'Khách hàng';
        $words = explode(' ', trim($name));
        $masked = [];
        foreach ($words as $w) {
            $len = mb_strlen($w, 'UTF-8');
            if ($len <= 2) {
                $masked[] = mb_substr($w, 0, 1, 'UTF-8') . '*';
            } else {
                $masked[] = mb_substr($w, 0, 1, 'UTF-8') . str_repeat('*', min(3, $len - 1));
            }
        }
        return implode(' ', $masked);
    }

    /**
     * Mask phone number for privacy (e.g. 0988123456 -> 098****456)
     */
    protected function maskPhone(?string $phone): string
    {
        if (empty($phone)) return '***';
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($clean) >= 9) {
            return substr($clean, 0, 3) . '****' . substr($clean, -3);
        }
        return substr($clean, 0, 2) . '****';
    }

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
                ? "Dạ ngày {$date} lúc {$time} nhà hàng vẫn còn bàn đẹp phù hợp cho {$people} khách ạ. Nhà hàng sẽ giữ bàn ưu tiên cho đoàn mình nếu anh/chị chốt giữ chỗ sớm nhé ạ!"
                : "Dạ khung giờ {$time} ngày {$date} đang có khá nhiều khách đặt chỗ. Em vẫn có thể hỗ trợ sắp xếp bàn ưu tiên cho mình nếu anh chị chốt giữ bàn sớm nhé ạ!"
        ];
    }

    /**
     * TOOL 3: Confirm and Create Booking in MySQL
     */
    protected function toolConfirmAndCreateBooking(array $args, array $context = []): array
    {
        $name = trim(strip_tags($args['name'] ?? ''));
        $phone = preg_replace('/[^0-9]/', '', trim($args['phone'] ?? ''));
        $people = (int)($args['number_of_people'] ?? 2);
        $date = trim($args['booking_date'] ?? Carbon::today()->toDateString());
        $time = trim($args['booking_time'] ?? '19:00');
        $note = trim(strip_tags($args['note'] ?? 'Đặt bàn qua Chatbot AI CIVILIZE'));

        // 1. Kiểm tra Tên
        if (empty($name) || mb_strlen($name, 'UTF-8') < 2 || mb_strlen($name, 'UTF-8') > 50) {
            return [
                'success' => false,
                'error' => 'Họ và tên người đặt bàn không hợp lệ (vui lòng nhập từ 2 đến 50 ký tự).'
            ];
        }

        // 2. Kiểm tra Số điện thoại (10 chữ số di động Việt Nam: 03, 05, 07, 08, 09)
        if (!preg_match('/^0[35789][0-9]{8}$/', $phone)) {
            return [
                'success' => false,
                'error' => 'Số điện thoại không đúng định dạng. Vui lòng cung cấp số di động gồm 10 chữ số (bắt đầu bằng 03, 05, 07, 08, 09).'
            ];
        }

        // 3. Kiểm tra Ngày đặt bàn
        try {
            $bookingDate = Carbon::parse($date)->startOfDay();
            $today = Carbon::today();
            if ($bookingDate->lt($today)) {
                return [
                    'success' => false,
                    'error' => 'Ngày đặt bàn không thể ở trong quá khứ. Vui lòng chọn ngày từ hôm nay trở đi.'
                ];
            }
            if ($bookingDate->gt($today->copy()->addDays(60))) {
                return [
                    'success' => false,
                    'error' => 'Nhà hàng chỉ nhận đặt bàn trước tối đa 60 ngày.'
                ];
            }
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => 'Định dạng ngày đặt bàn không hợp lệ (YYYY-MM-DD).'
            ];
        }

        // 4. Kiểm tra Giờ đặt bàn
        if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time)) {
            return [
                'success' => false,
                'error' => 'Định dạng giờ đặt bàn không hợp lệ (HH:MM).'
            ];
        }
        $timeParts = explode(':', $time);
        $timeMinutes = (int)$timeParts[0] * 60 + (int)$timeParts[1];
        if ($timeMinutes < 540 || $timeMinutes > 1290) { // 09:00 -> 21:30
            return [
                'success' => false,
                'error' => 'Giờ đặt bàn ngoài khung giờ tiếp nhận (nhà hàng phục vụ nhận bàn từ 09:00 đến 21:30).'
            ];
        }

        if ($bookingDate->isToday()) {
            $nowMinutes = Carbon::now()->hour * 60 + Carbon::now()->minute;
            if ($timeMinutes < $nowMinutes + 15) {
                return [
                    'success' => false,
                    'error' => 'Quý khách vui lòng đặt bàn trước giờ đến ít nhất 15-30 phút để nhà hàng kịp chuẩn bị bàn chu đáo.'
                ];
            }
        }

        // 5. Kiểm tra Số lượng khách
        if ($people < 1 || $people > 50) {
            return [
                'success' => false,
                'error' => 'Số lượng khách phải từ 1 đến 50 người. Với đoàn trên 50 người, quý khách vui lòng liên hệ trực tiếp hotline để được hỗ trợ sắp xếp khu tiệc riêng.'
            ];
        }

        if (mb_strlen($note, 'UTF-8') > 255) {
            $note = mb_substr($note, 0, 255, 'UTF-8');
        }

        // Tạo đơn đặt bàn thật trong DB
        $booking = Booking::create([
            'name' => $name,
            'email' => $context['user']['email'] ?? "{$phone}@civilize.local",
            'phone' => $phone,
            'number_of_people' => $people,
            'booking_date' => $bookingDate->toDateString(),
            'booking_time' => $time,
            'note' => $note,
            'status' => 1 // 1: Chờ xác nhận / Đã tiếp nhận
        ]);

        // Cập nhật Long-term Memory cho khách CHỈ KHI ĐÃ ĐĂNG NHẬP
        if (!empty($context['user']['id'])) {
            $this->updateCustomerProfile($context['user']['id'], $phone, [
                'customer_name' => $name,
                'total_visits_increment' => true
            ]);
        }

        return [
            'success' => true,
            'booking_id' => $booking->id,
            'name' => $name,
            'phone' => $this->maskPhone($phone),
            'people' => $people,
            'date' => $bookingDate->format('d/m/Y'),
            'time' => $time,
            'message' => "ĐÃ TẠO ĐƠN ĐẶT BÀN THÀNH CÔNG (Mã: #{$booking->id}). Nhà hàng sẽ gọi điện thoại xác nhận trong ít phút."
        ];
    }

    /**
     * TOOL 4: Lookup Order Status from MySQL (Strict Access Control & Data Masking)
     */
    protected function toolLookupOrderStatus(array $args, array $context = []): array
    {
        $phone = preg_replace('/[^0-9]/', '', trim($args['phone'] ?? ''));
        $orderId = !empty($args['order_id']) ? (int)$args['order_id'] : null;
        $userId = $context['user']['id'] ?? null;
        $hotline = $this->getSetting('hotline', '0338 990 538');

        // Nếu khách gõ chuỗi dạng #553
        if (!$orderId && !empty($args['order_code']) && preg_match('/#?([0-9]+)/', $args['order_code'], $m)) {
            $orderId = (int)$m[1];
        }

        // Trường hợp 1: Khách đã đăng nhập -> Chỉ cho phép tra cứu đơn của chính tài khoản mình
        if (!empty($userId)) {
            $query = Order::where('user_id', $userId);
            if ($orderId) {
                $query->where('id', $orderId);
            }
            $order = $query->orderBy('created_at', 'desc')->first();

            if (!$order) {
                $msg = $orderId 
                    ? "Dạ em kiểm tra không tìm thấy đơn hàng #{$orderId} trong lịch sử mua hàng của tài khoản anh/chị ạ."
                    : "Dạ tài khoản của anh/chị hiện chưa có đơn hàng nào trên hệ thống ạ.";
                return [
                    'found' => false,
                    'message' => $msg
                ];
            }
        } else {
            // Trường hợp 2: Khách vãng lai (Chưa đăng nhập) -> BẮT BUỘC cung cấp CẢ Mã đơn hàng VÀ Số điện thoại
            if (empty($orderId) || empty($phone)) {
                return [
                    'found' => false,
                    'message' => 'Vì lý do bảo mật thông tin đơn hàng, khi chưa đăng nhập quý khách vui lòng cung cấp đồng thời Mã đơn hàng (#...) và Số điện thoại nhận hàng đã dùng khi đặt đơn ạ!'
                ];
            }

            $order = Order::where('id', $orderId)
                ->where('phone', $phone)
                ->first();

            if (!$order) {
                return [
                    'found' => false,
                    'message' => "Dạ thông tin đơn hàng #{$orderId} và số điện thoại cung cấp không khớp với dữ liệu trên hệ thống. Anh/chị vui lòng kiểm tra lại hoặc liên hệ hotline {$hotline} để được hỗ trợ kiểm tra trực tiếp nhé ạ."
                ];
            }
        }

        $statusText = \App\Enums\OrderStatus::getLabel((int)$order->status, !empty($order->table_id));

        return [
            'found' => true,
            'order_id' => $order->id,
            'customer_name' => $this->maskName($order->name),
            'phone' => $this->maskPhone($order->phone),
            'total_price' => number_format($order->total_price ?? 0, 0, ',', '.') . '₫',
            'status' => $statusText,
            'order_style' => $order->order_style_name,
            'created_at' => $order->created_at ? $order->created_at->format('H:i - d/m/Y') : ''
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

        // 1. Tải Short-Term Memory (10 lượt hội thoại gần nhất của session này, theo thứ tự thời gian)
        $recentMessages = collect([]);
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('chatbot_messages')) {
                $recentMessages = ChatbotMessage::where('session_id', $sessionId)
                    ->orderBy('id', 'desc')
                    ->limit(10)
                    ->get()
                    ->reverse()
                    ->values();
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
            $hotline = $this->getSetting('hotline', '0338 990 538');
            $address = $this->getSetting('address', 'Cụm 1, TT. Phúc Thọ, Huyện Phúc Thọ, TP. Hà Nội');
            $hours = $this->getSetting('opening_hours', '09:00 - 23:00');

            // Build System Instruction với Strict Business Guardrails
            $systemInstruction = "Bạn là Nhân viên hỗ trợ trực tuyến của Nhà hàng CIVILIZE ({$address} - Hotline: {$hotline} - Giờ mở cửa: {$hours}).

NGUYÊN TẮC CỐT LÕI (BẮT BUỘC TUÂN THỦ TUYỆT ĐỐI):
1. TÔN TRỌNG DỮ LIỆU THỰC TẾ: Bạn KHÔNG được tự bịa ra món ăn, giá tiền hoặc tình trạng bàn. Hãy dùng công cụ 'search_menu_dishes' để lấy danh sách món và giá thật, dùng 'check_table_availability' để kiểm tra bàn trống.
2. CÂU HỎI MỞ / GỢI Ý MÓN / ĐẶC SẢN ('hôm nay có gì', 'hôm nay có món gì ngon', 'tư vấn món', 'đặc sản quán'):
   - Hãy đón tiếp khách niềm nở, chuyên nghiệp, giới thiệu 2-3 món signature nổi bật của nhà hàng (như Lẩu Thái, Lẩu bắp bò nhúng dấm, Sườn nướng BBQ) kèm mô tả hương vị hấp dẫn và giá tiền.
   - TUYỆT ĐỐI KHÔNG BAO GIỜ nói câu máy móc kiểu 'Không tìm thấy món đúng với từ khóa...'.
3. NẾU KHÁCH HỎI MÓN KHÔNG CÓ TRONG THỰC ĐƠN (ví dụ Pizza, Sushi, Bún bò Huế...): Lịch sự giải thích nhà hàng chuyên về ẩm thực Việt & Á (lẩu, nướng, mâm cơm gia đình, hải sản tươi sạch), sau đó dùng công cụ để gợi ý các món đặc sản có trong thực đơn.
4. QUY TRÌNH ĐẶT BÀN 2 BƯỚC:
   - Bước 1: Khi khách ngỏ ý đặt bàn -> Bắt buộc gọi 'check_table_availability'. Nếu còn bàn, tóm tắt thông tin và hỏi xin Họ Tên + SĐT để xác nhận giữ chỗ.
   - Bước 2: CHỈ ĐƯỢC gọi 'confirm_and_create_booking' khi khách ĐÃ ĐỒNG Ý xác nhận và cung cấp đầy đủ Tên, SĐT, Ngày, Giờ, Số khách. Tuyệt đối không tự ý đặt bàn khi khách mới chỉ hỏi bâng quơ.
5. TƯ VẤN KHẨU PHẦN & NGÂN SÁCH: Biết suy luận tính toán tổng tiền món ăn không vượt quá ngân sách khách yêu cầu. Nhắc khách ưu đãi giảm 10% áp dụng khi thực khách gọi món trực tiếp qua mã QR tại bàn nhà hàng.
6. PHONG CÁCH GIAO TIẾP: Xưng em, gọi khách là 'anh/chị'. Ấm áp, lịch thiệp, chu đáo, tinh tế chuẩn tác phong nhà hàng ẩm thực Việt Nam.";

            if (!empty($context['user'])) {
                $u = $context['user'];
                $systemInstruction .= "\n\nTHÔNG TIN KHÁCH HÀNG ĐANG ĐĂNG NHẬP:";
                if (!empty($u['name'])) $systemInstruction .= "\n• Họ và tên: {$u['name']}";
                if (!empty($u['phone'])) $systemInstruction .= "\n• Số điện thoại: {$this->maskPhone($u['phone'])}";
                $systemInstruction .= "\n(LƯU Ý: Khách đã đăng nhập. Bạn hãy chào hỏi thân thiết xưng hô theo tên khách (ví dụ: anh/chị {$u['name']}). Khi khách yêu cầu kiểm tra đơn hàng hoặc đặt bàn, bạn hãy hỗ trợ chu đáo).";
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

            // Header x-goog-api-key an toàn, loại bỏ query string key khỏi URL
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
            $headers = [
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $apiKey
            ];

            $response = Http::withHeaders($headers)->timeout(18)->post($url, [
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
                Log::warning('Gemini API call returned non-200: ' . preg_replace('/key=[a-zA-Z0-9_\-]+/', 'key=[REDACTED]', $response->body()));
                return null;
            }

            $data = $response->json();
            $candidate = $data['candidates'][0] ?? null;
            if (!$candidate) return null;

            $parts = $candidate['content']['parts'] ?? [];
            $functionCalls = [];
            foreach ($parts as $p) {
                if (!empty($p['functionCall'])) {
                    $functionCalls[] = $p['functionCall'];
                }
            }

            // KIỂM TRA XEM GEMINI CÓ GỌI TOOL (HỖ TRỢ NHIỀU TOOL MỖI LƯỢT)
            if (!empty($functionCalls)) {
                $responseParts = [];
                $toolOutputs = [];
                $allDishes = [];
                $primaryToolName = $functionCalls[0]['name'] ?? '';

                foreach ($functionCalls as $call) {
                    $toolName = $call['name'];
                    $toolArgs = $call['args'] ?? [];

                    // Thực thi Tool nội bộ trong Laravel
                    $toolResult = $this->executeTool($toolName, $toolArgs, $context);
                    $toolOutputs[$toolName] = $toolResult;

                    if ($toolName === 'search_menu_dishes' && !empty($toolResult['dishes'])) {
                        $allDishes = array_merge($allDishes, $toolResult['dishes']);
                    }

                    $responseParts[] = [
                        'functionResponse' => [
                            'name' => $toolName,
                            'response' => $toolResult
                        ]
                    ];
                }

                // Bảo toàn nguyên vẹn candidate content từ model (bao gồm thoughtSignature, thought tokens, call id)
                $candidateContent = $candidate['content'];
                if (!empty($candidateContent['parts'])) {
                    foreach ($candidateContent['parts'] as &$part) {
                        if (isset($part['functionCall']) && empty($part['functionCall']['args'])) {
                            $part['functionCall']['args'] = new \stdClass();
                        }
                    }
                    unset($part);
                }

                $contents[] = $candidateContent;
                $contents[] = [
                    'role' => 'user',
                    'parts' => $responseParts
                ];

                $secondResponse = Http::withHeaders($headers)->timeout(18)->post($url, [
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
                if (!empty($allDishes)) {
                    $products = $this->formatDishesForCard($allDishes);
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
                        if (!empty($allDishes)) {
                            $lowerFinal = mb_strtolower($finalText, 'UTF-8');
                            usort($allDishes, function($a, $b) use ($lowerFinal) {
                                $aMentioned = str_contains($lowerFinal, mb_strtolower($a['name'], 'UTF-8'));
                                $bMentioned = str_contains($lowerFinal, mb_strtolower($b['name'], 'UTF-8'));
                                if ($aMentioned === $bMentioned) return 0;
                                return $aMentioned ? -1 : 1;
                            });
                            $products = $this->formatDishesForCard($allDishes);
                        }

                        $firstResult = reset($toolOutputs);
                        return [
                            'reply' => trim($finalText),
                            'products' => $products,
                            'tool_calls' => $functionCalls,
                            'tool_output' => $toolOutputs,
                            'quick_replies' => $this->generateContextualQuickReplies($primaryToolName, $firstResult ?: [])
                        ];
                    }
                } else {
                    Log::warning('Gemini 2nd Turn Error: ' . preg_replace('/key=[a-zA-Z0-9_\-]+/', 'key=[REDACTED]', $secondResponse->body()));
                }

                // Graceful fallback nếu 2nd turn không sinh được text
                $fallbackReply = "Dạ em đã kiểm tra thông tin cho mình rồi ạ!";
                $firstResult = reset($toolOutputs);
                if ($primaryToolName === 'search_menu_dishes') {
                    $fallbackReply = "Dạ nhà hàng CIVILIZE bên em có rất nhiều món ăn đặc sắc và các món lẩu, nướng thơm ngon được đông đảo thực khách yêu thích. Em xin gửi anh/chị tham khảo danh sách bên dưới nhé ạ!";
                } elseif ($primaryToolName === 'check_table_availability') {
                    $fallbackReply = ($firstResult['message'] ?? 'Dạ nhà hàng hiện vẫn còn bàn phục vụ quý khách ạ.') . "\n\nĐể em giữ vị trí bàn đẹp nhất cho đoàn mình, anh chị cho em xin Họ tên và Số điện thoại nhé ạ!";
                } elseif ($primaryToolName === 'lookup_order_status') {
                    if (!empty($firstResult['found'])) {
                        $fallbackReply = "Dạ thông tin đơn hàng của mình:\n\n" .
                                         "• Mã đơn: #{$firstResult['order_id']}\n" .
                                         "• Khách hàng: {$firstResult['customer_name']}\n" .
                                         "• Trạng thái: {$firstResult['status']}\n" .
                                         "• Tổng tiền: {$firstResult['total_price']}\n" .
                                         "• Thời gian đặt: {$firstResult['created_at']}";
                    } else {
                        $fallbackReply = $firstResult['message'] ?? 'Dạ để tra cứu thông tin đơn hàng, anh/chị vui lòng cung cấp Mã đơn hàng (#...) và Số điện thoại nhận hàng giúp em nhé ạ!';
                    }
                } elseif ($primaryToolName === 'confirm_and_create_booking') {
                    $fallbackReply = $firstResult['message'] ?? 'Dạ em đã ghi nhận thông tin đặt bàn của anh/chị rồi ạ!';
                } elseif (!empty($firstResult['message'])) {
                    $fallbackReply = $firstResult['message'];
                }

                return [
                    'reply' => $fallbackReply,
                    'products' => $products,
                    'tool_calls' => $functionCalls,
                    'tool_output' => $toolOutputs,
                    'quick_replies' => $this->generateContextualQuickReplies($primaryToolName, $firstResult ?: [])
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
                        ['title' => 'Đặt bàn giữ chỗ', 'payload' => 'Tôi muốn đặt bàn'],
                        ['title' => 'Địa chỉ & Giờ mở cửa', 'payload' => 'Địa chỉ quán ở đâu?']
                    ]
                ];
            }

        } catch (\Exception $e) {
            Log::error('Gemini Agent Error: ' . preg_replace('/key=[a-zA-Z0-9_\-]+/', 'key=[REDACTED]', $e->getMessage()));
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
                    ['title' => 'Đặt bàn giữ chỗ', 'payload' => 'Tôi muốn đặt bàn']
                ]
            ];
        }

        // Tình huống 2: Khách cung cấp thông tin hoặc xác nhận đặt bàn
        preg_match('/(0[35789][0-9]{8})/u', $userMsg, $phoneMatch);
        $extractedPhone = $phoneMatch[1] ?? null;

        $hasBookingKeywords = $this->matchAny($lower, ['đặt bàn', 'dat ban', 'giữ bàn', 'giu ban', 'book bàn', 'book ban', 'chốt bàn', 'đặt chỗ', 'dat cho']);
        $botAskedForBooking = (str_contains($lastBotMsg, 'xác nhận giữ bàn') 
            || str_contains($lastBotMsg, 'họ tên và số điện thoại') 
            || str_contains($lastBotMsg, 'tên và số điện thoại')
            || str_contains($lastBotMsg, 'để em hoàn tất giữ bàn'));
        $userConfirmed = $this->matchAny($lower, ['ok', 'đồng ý', 'dong y', 'chốt nhé', 'chot nhe', 'đặt đi', 'dat di', 'xác nhận', 'xac nhan', 'đúng rồi', 'dung roi', 'đặt luôn']);
        $explicitConfirm = $this->matchAny($lower, [
            'xác nhận đặt bàn', 'xac nhan dat ban', 'xác nhận giữ bàn', 'xac nhan giu ban',
            'đồng ý đặt bàn', 'dong y dat ban', 'chốt đặt bàn', 'chot dat ban',
            'đặt luôn nhé', 'dat luon nhe', 'đồng ý', 'dong y', 'xác nhận', 'xac nhan', 'chốt nhé', 'chot nhe'
        ]);

        // CHỐNG SPAM BOOKING:
        // 1. Nếu bot đã hỏi giữ bàn và khách cung cấp SĐT hoặc xác nhận -> Chốt đơn
        // 2. Nếu khách nói đặt bàn kèm SĐT VÀ có câu xác nhận rõ ràng -> Chốt đơn
        // 3. Nếu khách chỉ nói đặt bàn kèm SĐT nhưng chưa xác nhận -> Hỏi lại xác nhận trước khi tạo đơn
        $isBookingConfirm = ($botAskedForBooking && ($extractedPhone || $userConfirmed))
            || ($hasBookingKeywords && $extractedPhone && $explicitConfirm);

        // Trường hợp khách nhắn đặt bàn kèm SĐT lần đầu nhưng chưa có từ khóa xác nhận: Hỏi lại xác nhận
        if ($hasBookingKeywords && $extractedPhone && !$isBookingConfirm) {
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

            $availResult = $this->toolCheckTableAvailability([
                'number_of_people' => $people,
                'booking_date' => $date,
                'booking_time' => $time
            ]);

            return [
                'reply' => "{$availResult['message']}\n\n" .
                           "Dạ em đã ghi nhận thông tin đặt bàn của quý khách:\n" .
                           "• Số điện thoại: {$extractedPhone}\n" .
                           "• Số lượng: {$people} khách\n" .
                           "• Thời gian: {$time} ngày {$date}\n\n" .
                           "Quý khách vui lòng bấm 'Xác nhận đặt bàn' bên dưới hoặc nhắn 'Đồng ý' để em hoàn tất giữ bàn nhé ạ!",
                'products' => [],
                'quick_replies' => [
                    ['title' => 'Xác nhận đặt bàn', 'payload' => 'Xác nhận đặt bàn'],
                    ['title' => 'Đổi giờ đặt bàn', 'payload' => 'Tôi muốn đổi giờ'],
                    ['title' => 'Xem thực đơn', 'payload' => 'Xem thực đơn']
                ]
            ];
        }

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
                // Tạo đơn đặt bàn với validation đầy đủ
                $bookingResult = $this->toolConfirmAndCreateBooking([
                    'name' => $customerName,
                    'phone' => $extractedPhone,
                    'number_of_people' => $people,
                    'booking_date' => $date,
                    'booking_time' => $time,
                    'note' => 'Đặt bàn qua Chatbot AI CIVILIZE'
                ], $context);

                if (!$bookingResult['success']) {
                    return [
                        'reply' => "Dạ " . ($bookingResult['error'] ?? 'thông tin đặt bàn chưa chính xác, anh/chị vui lòng kiểm tra lại ạ.') . "\n\nAnh/chị vui lòng cung cấp lại thông tin hợp lệ để em hoàn tất giữ bàn nhé ạ!",
                        'products' => [],
                        'quick_replies' => [
                            ['title' => 'Đặt bàn lại', 'payload' => 'Tôi muốn đặt bàn'],
                            ['title' => 'Hotline hỗ trợ', 'payload' => 'Hotline của quán']
                        ]
                    ];
                }

                return [
                    'reply' => "Dạ em đã hoàn tất đặt bàn cho mình rồi ạ!\n\n" .
                               "• Mã đặt bàn: #{$bookingResult['booking_id']}\n" .
                               "• Khách hàng: {$customerName} ({$bookingResult['phone']})\n" .
                               "• Số lượng: {$people} khách\n" .
                               "• Thời gian: {$time} ngày {$bookingResult['date']}\n\n" .
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

        // Tình huống 2.1: Tin nhắn có SĐT nhưng KHÔNG có ý định đặt bàn (tránh tự động tạo booking)
        if ($extractedPhone && !$isBookingConfirm && empty($bMatch[1]) && empty($hasSpecificFoodConsult)) {
            if ($this->matchAny($lower, ['đơn', 'don', 'tra cứu', 'kiem tra', 'order'])) {
                $orderId = null;
                if (preg_match('/#?([0-9]+)/', $userMsg, $om)) {
                    $orderId = (int)$om[1];
                }
                $orderResult = $this->toolLookupOrderStatus([
                    'phone' => $extractedPhone,
                    'order_id' => $orderId
                ], $context);

                return [
                    'reply' => $orderResult['found'] 
                        ? "Dạ thông tin đơn hàng của mình:\n\n• Mã đơn: #{$orderResult['order_id']}\n• Khách hàng: {$orderResult['customer_name']}\n• Tổng tiền: {$orderResult['total_price']}\n• Trạng thái: {$orderResult['status']}\n• Thời gian: {$orderResult['created_at']}"
                        : $orderResult['message'],
                    'products' => [],
                    'quick_replies' => [
                        ['title' => 'Xem thực đơn', 'payload' => 'Xem thực đơn'],
                        ['title' => 'Hotline quán', 'payload' => 'Hotline của quán']
                    ]
                ];
            }

            return [
                'reply' => "Dạ em đã nhận được số điện thoại {$extractedPhone} của anh/chị. Anh/chị đang cần hỗ trợ tra cứu tiến độ đơn hàng hay muốn đặt bàn trước tại CIVILIZE ạ?",
                'products' => [],
                'quick_replies' => [
                    ['title' => 'Tôi muốn đặt bàn', 'payload' => 'Tôi muốn đặt bàn'],
                    ['title' => 'Tra cứu đơn hàng', 'payload' => 'Tra cứu đơn hàng'],
                    ['title' => 'Xem thực đơn', 'payload' => 'Xem thực đơn']
                ]
            ];
        }

        // Tình huống 2.2: Yêu cầu tra cứu đơn hàng tổng quát (không kèm SĐT trong tin nhắn)
        $hasOrderLookupIntent = $this->matchAny($lower, [
            'kiểm tra đơn', 'kiem tra don', 'tra cứu đơn', 'tra cuu don', 'xem đơn', 'xem don',
            'tình trạng đơn', 'tinh trang don', 'đơn hàng của tôi', 'don hang cua toi',
            'check đơn', 'check don', 'kiểm tra đơn hàng', 'tra cứu đơn hàng'
        ]);

        if ($hasOrderLookupIntent && empty($hasSpecificFoodConsult)) {
            $orderId = null;
            if (preg_match('/#?([0-9]+)/', $userMsg, $om)) {
                $orderId = (int)$om[1];
            }

            $orderResult = $this->toolLookupOrderStatus([
                'order_id' => $orderId,
                'phone' => $extractedPhone
            ], $context);

            if (!empty($orderResult['found'])) {
                $reply = "Dạ thông tin đơn hàng #{$orderResult['order_id']} của anh/chị:\n\n" .
                         "• Khách hàng: {$orderResult['customer_name']}\n" .
                         "• Trạng thái: {$orderResult['status']}\n" .
                         "• Tổng tiền: {$orderResult['total_price']}\n" .
                         "• Thời gian đặt: {$orderResult['created_at']}";
            } else {
                $reply = $orderResult['message'] ?? 'Dạ để tra cứu thông tin đơn hàng, anh/chị vui lòng cung cấp Mã đơn hàng (#...) và Số điện thoại nhận hàng đã dùng khi đặt đơn giúp em nhé ạ!';
            }

            return [
                'reply' => $reply,
                'products' => [],
                'quick_replies' => [
                    ['title' => 'Xem thực đơn', 'payload' => 'Xem thực đơn'],
                    ['title' => 'Đặt bàn giữ chỗ', 'payload' => 'Tôi muốn đặt bàn'],
                    ['title' => 'Hotline quán', 'payload' => 'Hotline của quán']
                ]
            ];
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
                'reply' => $intro . "Anh chị có thể tham khảo danh sách món ăn thực tế bên dưới hoặc bấm đặt bàn giữ chỗ trước để được đón tiếp chu đáo nhất nhé ạ! (Ưu đãi giảm 10% áp dụng khi thực khách gọi món qua mã QR tại bàn)",
                'products' => $this->formatDishesForCard($dishes),
                'quick_replies' => [
                    ['title' => 'Đặt bàn giữ chỗ', 'payload' => 'Tôi muốn đặt bàn'],
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
                           "Để em hoàn tất giữ bàn vị trí đẹp nhất cho đoàn mình, anh chị cho em xin Họ tên và Số điện thoại liên hệ nhé ạ!",
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
                            ['title' => 'Đặt bàn giữ chỗ', 'payload' => 'Tôi muốn đặt bàn'],
                            ['title' => 'Gợi ý món ngon', 'payload' => 'Gợi ý món ngon hôm nay'],
                            ['title' => 'Hotline nhà hàng', 'payload' => 'Hotline của quán']
                        ]
                    ];
                }
            }
        }

        // Mặc định: Lời chào thân thiện và gợi ý tác vụ
        return [
            'reply' => "Dạ em chào anh/chị ạ! Em là nhân viên tư vấn của CIVILIZE Restaurant. Em luôn sẵn sàng hỗ trợ gợi ý món ngon hợp khẩu vị, kiểm tra bàn trống, đặt bàn giữ chỗ hoặc giải đáp các thắc mắc của quán. Hôm nay em có thể hỗ trợ gì cho mình ạ?",
            'products' => [],
            'quick_replies' => [
                ['title' => 'Gợi ý món ngon hôm nay', 'payload' => 'Gợi ý món ngon hôm nay'],
                ['title' => 'Đặt bàn giữ chỗ', 'payload' => 'Tôi muốn đặt bàn'],
                ['title' => 'Địa chỉ & Giờ mở cửa', 'payload' => 'Địa chỉ quán ở đâu?']
            ]
        ];
    }

    /**
     * Helpers: Short-term memory saver with PII masking
     */
    protected function saveMessageToMemory(string $sessionId, ?int $userId, string $role, string $content, ?array $toolCalls = null, ?array $toolOutput = null): void
    {
        try {
            // Khử dữ liệu cá nhân nhạy cảm trong toolOutput trước khi lưu vào DB
            $sanitizedOutput = $toolOutput;
            if (is_array($sanitizedOutput)) {
                if (isset($sanitizedOutput['phone'])) {
                    $sanitizedOutput['phone'] = $this->maskPhone($sanitizedOutput['phone']);
                }
                if (isset($sanitizedOutput['customer_name'])) {
                    $sanitizedOutput['customer_name'] = $this->maskName($sanitizedOutput['customer_name']);
                }
            }

            ChatbotMessage::create([
                'session_id' => $sessionId,
                'user_id' => $userId,
                'role' => $role,
                'content' => $content,
                'tool_calls' => $toolCalls,
                'tool_output' => $sanitizedOutput
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to save chatbot message memory: ' . $e->getMessage());
        }
    }

    /**
     * Helpers: Long-term profile updater (Protected against poisoning)
     */
    protected function updateCustomerProfile(?int $userId, ?string $phone, array $data): void
    {
        // Chống đầu độc hồ sơ: Chỉ cập nhật khi user đã đăng nhập xác thực
        if (empty($userId)) {
            return;
        }

        try {
            $profile = CustomerAiProfile::firstOrNew(['user_id' => $userId]);
            if ($phone && preg_match('/^0[35789][0-9]{8}$/', $phone)) {
                $profile->phone = $phone;
            }
            if (!empty($data['customer_name'])) {
                $profile->customer_name = strip_tags(trim($data['customer_name']));
            }
            if (!empty($data['total_visits_increment'])) {
                $profile->total_visits = ($profile->total_visits ?? 0) + 1;
            }
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
                    ['title' => 'Đặt bàn giữ chỗ', 'payload' => 'Tôi muốn đặt bàn'],
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
            case 'lookup_order_status':
                return [
                    ['title' => 'Xem thực đơn', 'payload' => 'Xem thực đơn'],
                    ['title' => 'Đặt bàn giữ chỗ', 'payload' => 'Tôi muốn đặt bàn'],
                    ['title' => 'Hotline quán', 'payload' => 'Hotline của quán']
                ];
            default:
                return [
                    ['title' => 'Gợi ý món ngon hôm nay', 'payload' => 'Gợi ý món ngon hôm nay'],
                    ['title' => 'Đặt bàn giữ chỗ', 'payload' => 'Tôi muốn đặt bàn'],
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
