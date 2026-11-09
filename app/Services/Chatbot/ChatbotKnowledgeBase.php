<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\File;

class ChatbotKnowledgeBase
{
    protected array $knowledgeBase = [];

    public function __construct()
    {
        $this->initBaseKnowledge();
        $this->syncDynamicKnowledge();
    }

    protected function initBaseKnowledge(): void
    {
        $this->knowledgeBase = [
            'location_and_hours' => [
                'title' => 'Địa chỉ & Giờ mở cửa',
                'keywords' => ['địa chỉ', 'dia chi', 'ở đâu', 'o dau', 'giờ mở cửa', 'gio mo cua', 'mở cửa mấy giờ', 'mo cua', 'chi nhánh', 'vi trí', 'vi tri', 'maps', 'google map', 'đường đi'],
                'content' => 'CIVILIZE Restaurant toạ lạc tại 123 Đường Ẩm Thực, Quận 1, TP. Hồ Chí Minh. Thời gian phục vụ đón khách từ 10:00 sáng đến 23:00 đêm mỗi ngày (từ Thứ Hai đến Chủ Nhật, kể cả các ngày Lễ/Tết).'
            ],
            'parking_and_space' => [
                'title' => 'Bãi đỗ xe & Không gian quán',
                'keywords' => ['bãi xe', 'bai xe', 'đỗ xe', 'do xe', 'gửi xe', 'gui xe', 'ô tô', 'o to', 'xe hơi', 'xe hoi', 'không gian', 'khong gian', 'phòng riêng', 'vip', 'view', 'sức chứa'],
                'content' => 'Nhà hàng sở hữu bãi đậu xe ô tô và xe máy rộng rãi, có bảo vệ trông giữ 24/7 MIỄN PHÍ. Không gian gồm 3 tầng hiện đại: Tầng trệt náo nhiệt, Tầng lửng ấm cúng và Hệ thống 6 phòng VIP riêng tư máy lạnh cho tiệc gia đình, tiếp khách doanh nghiệp.'
            ],
            'delivery_and_shipping' => [
                'title' => 'Chính sách giao hàng & Ship đồ ăn',
                'keywords' => ['giao hàng', 'giao hang', 'ship', 'phí ship', 'phi ship', 'freeship', 'đặt về', 'mang về', 'mang ve', 'tận nơi', 'tan noi'],
                'content' => 'Nhà hàng phục vụ giao món tận nơi siêu tốc trong vòng 30–45 phút với hộp giữ nhiệt chuyên dụng. MIỄN PHÍ vận chuyển toàn bộ cho đơn hàng từ 500.000₫ trong bán kính 15km.'
            ],
            'booking_policy' => [
                'title' => 'Chính sách đặt bàn & Giữ chỗ',
                'keywords' => ['đặt bàn', 'dat ban', 'giữ chỗ', 'giu cho', 'tiệc', 'tiec', 'sinh nhật', 'sinh nhat', 'liên hoan', 'lien hoan', 'cọc', 'đặt cọc'],
                'content' => 'Nhà hàng khuyến khích quý khách đặt bàn trước ít nhất 30 phút để nhận vị trí đẹp nhất. Hoàn toàn KHÔNG YÊU CẦU ĐẶT CỌC cho bàn dưới 10 khách. Với tiệc đông người hoặc đặt phòng VIP lớn, quản lý nhà hàng sẽ liên hệ xác nhận chi tiết.'
            ],
            'hygiene_and_ingredients' => [
                'title' => 'An toàn thực phẩm & Nguồn nguyên liệu',
                'keywords' => ['an toàn', 'an toan', 'vệ sinh', 've sinh', 'sạch', 'nguồn gốc', 'nguon goc', 'thịt bò', 'hải sản', 'tươi sống', 'hóa đơn đỏ', 'vat'],
                'content' => '100% nguyên liệu thịt bò thượng hạng nhập khẩu chuẩn VietGAP/USDA, hải sản bơi tại hồ kính lấy tươi trong ngày. Quy trình bếp 1 chiều khép kín đạt chuẩn VSATTP TP.HCM. Nhà hàng hỗ trợ xuất hoá đơn đỏ VAT điện tử ngay trong ngày.'
            ]
        ];
    }

    public function syncDynamicKnowledge(): void
    {
        try {
            $settingsPath = storage_path('app/settings.json');
            if (File::exists($settingsPath)) {
                $settings = json_decode(File::get($settingsPath), true);
                if (!empty($settings['address'])) {
                    $this->knowledgeBase['location_and_hours']['content'] =
                        "CIVILIZE Restaurant toạ lạc tại {$settings['address']}. " .
                        "Giờ phục vụ: " . ($settings['opening_hours'] ?? '10:00 - 23:00') . " hàng ngày. " .
                        "Hotline hỗ trợ: " . ($settings['hotline'] ?? '0338 990 538') . ".";
                }
                if (!empty($settings['shipping_free_threshold'])) {
                    $freeShipThreshold = number_format($settings['shipping_free_threshold'], 0, ',', '.') . '₫';
                    $maxKm = $settings['shipping_max_distance_km'] ?? 15;
                    $this->knowledgeBase['delivery_and_shipping']['content'] =
                        "Nhà hàng phục vụ giao món tận nơi siêu tốc trong vòng 30–45 phút với hộp giữ nhiệt chuyên dụng. " .
                        "MIỄN PHÍ vận chuyển toàn bộ cho đơn hàng từ {$freeShipThreshold} trong bán kính {$maxKm}km.";
                }
            }
        } catch (\Throwable $e) {}
    }

    public function getAll(): array
    {
        return $this->knowledgeBase;
    }

    public function findMatch(string $userMsg): ?array
    {
        $cleanMsg = mb_strtolower(trim($userMsg), 'UTF-8');
        foreach ($this->knowledgeBase as $key => $chunk) {
            foreach ($chunk['keywords'] as $kw) {
                if (str_contains($cleanMsg, $kw)) {
                    return [
                        'key' => $key,
                        'title' => $chunk['title'],
                        'content' => $chunk['content']
                    ];
                }
            }
        }
        return null;
    }
}
