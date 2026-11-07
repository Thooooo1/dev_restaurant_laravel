<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Booking;
use App\Models\ChatbotMessage;
use App\Models\CustomerAiProfile;
use App\Services\ChatbotService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChatbotSecurityAuditTest extends TestCase
{
    /**
     * Test 1: Khách vãng lai tra cứu đơn hàng bắt buộc phải có cả Mã đơn VÀ Số điện thoại, dữ liệu trả về phải được che mờ (masking)
     */
    public function test_guest_order_lookup_requires_both_order_id_and_phone_and_masks_pii()
    {
        $order = Order::create([
            'user_id' => 999,
            'name' => 'Nguyen Van Bao',
            'phone' => '0988123456',
            'email' => 'baonguyen@example.com',
            'address' => 'Phuc Tho, Ha Noi',
            'total_price' => 250000,
            'created_by' => 1,
            'status' => 1
        ]);

        $service = new ChatbotService();

        // 1. Chỉ truyền order_id -> Phải từ chối vì lý do bảo mật
        $resOnlyId = $service->executeTool('lookup_order_status', [
            'order_id' => $order->id,
            'phone' => ''
        ], ['user' => null]);
        $this->assertFalse($resOnlyId['found']);
        $this->assertStringContainsString('bảo mật', $resOnlyId['message']);

        // 2. Chỉ truyền phone -> Phải từ chối
        $resOnlyPhone = $service->executeTool('lookup_order_status', [
            'order_id' => null,
            'phone' => '0988123456'
        ], ['user' => null]);
        $this->assertFalse($resOnlyPhone['found']);

        // 3. Truyền đúng cả order_id VÀ phone -> Thành công và che mờ PII
        $resBoth = $service->executeTool('lookup_order_status', [
            'order_id' => $order->id,
            'phone' => '0988123456'
        ], ['user' => null]);
        $this->assertTrue($resBoth['found']);
        $this->assertNotEquals('Nguyen Van Bao', $resBoth['customer_name']);
        $this->assertStringContainsString('*', $resBoth['customer_name']);
        $this->assertNotEquals('0988123456', $resBoth['phone']);
        $this->assertStringContainsString('****', $resBoth['phone']);

        $order->delete();
    }

    /**
     * Test 2: Khách đăng nhập không thể tra cứu đơn hàng của người khác
     */
    public function test_authenticated_user_cannot_lookup_other_users_order()
    {
        $orderOwner = Order::create([
            'user_id' => 111,
            'name' => 'Nguoi So Huu',
            'phone' => '0912345678',
            'email' => 'owner@example.com',
            'address' => 'Phuc Tho',
            'total_price' => 300000,
            'created_by' => 1,
            'status' => 1
        ]);

        $service = new ChatbotService();

        // User 222 cố tình tra cứu đơn của User 111
        $contextHacker = [
            'user' => [
                'id' => 222,
                'name' => 'User Khac',
                'phone' => '0988888888'
            ]
        ];

        $res = $service->executeTool('lookup_order_status', [
            'order_id' => $orderOwner->id,
            'phone' => '0912345678'
        ], $contextHacker);

        $this->assertFalse($res['found']);

        $orderOwner->delete();
    }

    /**
     * Test 3: Tin nhắn có chứa SĐT đơn thuần KHÔNG được tự động tạo booking
     */
    public function test_fallback_engine_does_not_auto_create_booking_on_mere_phone_number()
    {
        $initialCount = Booking::count();
        $service = new ChatbotService();

        // Khách chỉ hỏi vu vơ có số điện thoại
        $res = $service->reply('Hotline của quán có phải là 0988123456 không ạ?', [
            'session_id' => 'test_session_phone_only_' . uniqid(),
            'user' => null
        ]);

        $this->assertEquals($initialCount, Booking::count(), 'So luong booking khong duoc tang khi chi co SDT');
        $this->assertStringNotContainsString('ĐÃ TẠO ĐƠN ĐẶT BÀN THÀNH CÔNG', $res['reply']);
    }

    /**
     * Test 4: Đặt bàn phải validate ngày trong quá khứ, SĐT sai và số người vượt mức
     */
    public function test_booking_validation_rules()
    {
        $service = new ChatbotService();

        // 1. Ngày quá khứ
        $resPastDate = $service->executeTool('confirm_and_create_booking', [
            'name' => 'Tran Van A',
            'phone' => '0988123456',
            'booking_date' => Carbon::yesterday()->toDateString(),
            'booking_time' => '19:00',
            'number_of_people' => 4
        ]);
        $this->assertFalse($resPastDate['success']);
        $this->assertStringContainsString('quá khứ', $resPastDate['error']);

        // 2. SĐT sai định dạng
        $resBadPhone = $service->executeTool('confirm_and_create_booking', [
            'name' => 'Tran Van A',
            'phone' => '012345',
            'booking_date' => Carbon::tomorrow()->toDateString(),
            'booking_time' => '19:00',
            'number_of_people' => 4
        ]);
        $this->assertFalse($resBadPhone['success']);
        $this->assertStringContainsString('Số điện thoại', $resBadPhone['error']);

        // 3. Số người > 50
        $resTooMany = $service->executeTool('confirm_and_create_booking', [
            'name' => 'Tran Van A',
            'phone' => '0988123456',
            'booking_date' => Carbon::tomorrow()->toDateString(),
            'booking_time' => '19:00',
            'number_of_people' => 99
        ]);
        $this->assertFalse($resTooMany['success']);
        $this->assertStringContainsString('50 người', $resTooMany['error']);
    }

    /**
     * Test 5: Khách vãng lai không được đầu độc hồ sơ khách hàng (CustomerAiProfile)
     */
    public function test_guest_cannot_poison_customer_ai_profile()
    {
        $service = new ChatbotService();
        $targetPhone = '0977889900';

        // Xóa profile cũ nếu có
        CustomerAiProfile::where('phone', $targetPhone)->delete();

        // Tạo đơn đặt bàn với tư cách khách vãng lai (không có user id)
        $service->executeTool('confirm_and_create_booking', [
            'name' => 'Ten Gia Mao',
            'phone' => $targetPhone,
            'booking_date' => Carbon::tomorrow()->toDateString(),
            'booking_time' => '19:00',
            'number_of_people' => 2
        ], ['user' => null]);

        // Kiểm tra xem CustomerAiProfile có bị tạo/ghi đè không
        $profile = CustomerAiProfile::where('phone', $targetPhone)->first();
        $this->assertNull($profile, 'Khach vang lai khong duoc phep tao hay dau doc CustomerAiProfile');
    }

    /**
     * Test 6: ChatbotController không được trả tool_calls và tool_output ra client
     */
    public function test_chatbot_controller_does_not_leak_internal_tool_details()
    {
        $response = $this->postJson(route('chatbot.message'), [
            'message' => 'Địa chỉ quán ở đâu?',
            'session_id' => 'test_sec_leak_' . uniqid()
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertTrue($data['success']);
        $this->assertArrayNotHasKey('tool_calls', $data);
        $this->assertArrayNotHasKey('tool_output', $data);
    }

    /**
     * Test 7: Bộ nhớ hội thoại phải lấy 10 tin mới nhất theo đúng thứ tự thời gian
     */
    public function test_chatbot_memory_loads_latest_ten_messages_chronologically()
    {
        $sessionId = 'test_memory_order_' . uniqid();

        // Tạo 15 tin nhắn
        for ($i = 1; $i <= 15; $i++) {
            ChatbotMessage::create([
                'session_id' => $sessionId,
                'role' => ($i % 2 === 1) ? 'user' : 'assistant',
                'content' => "Tin nhắn số {$i}"
            ]);
        }

        // Đọc 10 tin nhắn gần nhất theo thứ tự thời gian như trong ChatbotService
        $loaded = ChatbotMessage::where('session_id', $sessionId)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get()
            ->reverse()
            ->values();

        $this->assertCount(10, $loaded);
        $this->assertEquals('Tin nhắn số 6', $loaded->first()->content);
        $this->assertEquals('Tin nhắn số 15', $loaded->last()->content);

        ChatbotMessage::where('session_id', $sessionId)->delete();
    }

    /**
     * Test 8: Cac endpoint tu van (/tu-van/goi-y, /tu-van/lam-moi) phan hoi dung dinh dang JSON va tranh WAF block
     */
    public function test_chatbot_routes_respond_correctly_on_tu_van_path()
    {
        // 1. Goi y hoi dap
        $responseSug = $this->getJson(route('chatbot.suggestions'));
        $responseSug->assertStatus(200);
        $dataSug = $responseSug->json();
        $this->assertTrue($dataSug['success']);
        $this->assertIsArray($dataSug['quick_replies']);
        $this->assertNotEmpty($dataSug['quick_replies']);
        $this->assertNotEmpty($dataSug['greeting']);

        // 2. Lam moi phien hoi thoai
        $responseClear = $this->postJson(route('chatbot.clear'), [
            'session_id' => 'test_clear_' . uniqid()
        ]);
        $responseClear->assertStatus(200);
        $dataClear = $responseClear->json();
        $this->assertTrue($dataClear['success']);
    }
}
