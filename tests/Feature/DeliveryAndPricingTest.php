<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Services\PricingService;

class DeliveryAndPricingTest extends TestCase
{
    /**
     * Kiểm tra tính toán phí giao hàng qua PricingService
     */
    public function test_pricing_service_calculates_shipping_fee_correctly(): void
    {
        $settings = PricingService::getShippingSettings();
        $freeThreshold = $settings['free_threshold'];
        $baseFee = $settings['base_fee'];

        // Đơn hàng dưới mức miễn phí -> tính phí ship cơ bản
        $feeBelow = PricingService::calculateShippingFee($freeThreshold - 10000);
        $this->assertEquals($baseFee, $feeBelow);

        // Đơn hàng đạt hoặc vượt mức miễn phí -> 0đ
        $feeAbove = PricingService::calculateShippingFee($freeThreshold + 50000);
        $this->assertEquals(0, $feeAbove);
    }

    /**
     * Kiểm tra tổng hợp giỏ hàng và chiết khấu tại bàn
     */
    public function test_pricing_service_cart_summary(): void
    {
        $mockCart = [
            1 => ['price' => 100000, 'qty' => 2],
            2 => ['price' => 150000, 'qty' => 1],
        ];
        // Subtotal = 200k + 150k = 350k

        // 1. Đơn giao hàng (Delivery)
        $summaryDelivery = PricingService::calculateCartSummary($mockCart, false);
        $this->assertEquals(3, $summaryDelivery['count']);
        $this->assertEquals(350000, $summaryDelivery['subtotal']);
        $this->assertEquals(0, $summaryDelivery['discount']);
        $this->assertEquals(25000, $summaryDelivery['shipping_fee']);
        $this->assertEquals(375000, $summaryDelivery['total']);

        // 2. Đơn tại bàn (Dine-in): Không tính ship, chiết khấu 10%
        $summaryDineIn = PricingService::calculateCartSummary($mockCart, true);
        $this->assertEquals(35000, $summaryDineIn['discount']);
        $this->assertEquals(0, $summaryDineIn['shipping_fee']);
        $this->assertEquals(315000, $summaryDineIn['total']);
    }

    /**
     * Kiểm tra chuẩn hóa thông tin phương thức thanh toán qua Enum PaymentMethod
     */
    public function test_payment_method_enum_resolves_all_types(): void
    {
        $cash = PaymentMethod::getInfo(1);
        $this->assertEquals('cash', $cash['code']);
        $this->assertEquals('Tiền mặt', $cash['name']);

        $vietqr = PaymentMethod::getInfo(2);
        $this->assertEquals('vietqr', $vietqr['code']);
        $this->assertEquals('Chuyển khoản VietQR', $vietqr['name']);

        $card = PaymentMethod::getInfo(3);
        $this->assertEquals('card', $card['code']);
        $this->assertEquals('Quẹt thẻ POS', $card['name']);
    }

    /**
     * Kiểm tra nhãn trạng thái giao hàng của model Order
     */
    public function test_order_delivery_status_label(): void
    {
        $order = new Order();

        $order->delivery_status = Order::DELIVERY_WAITING;
        $this->assertEquals('Chờ phân công shipper', $order->delivery_status_label);

        $order->delivery_status = Order::DELIVERY_PICKED_UP;
        $this->assertEquals('Shipper đã nhận hàng', $order->delivery_status_label);

        $order->delivery_status = Order::DELIVERY_DELIVERING;
        $this->assertEquals('Đang giao hàng', $order->delivery_status_label);

        $order->delivery_status = Order::DELIVERY_DELIVERED;
        $this->assertEquals('Đã giao thành công', $order->delivery_status_label);

        $order->delivery_status = Order::DELIVERY_FAILED;
        $this->assertEquals('Giao hàng thất bại', $order->delivery_status_label);
    }

    /**
     * Kiểm tra logic chuyển trạng thái giao hàng và đồng bộ OrderStatus chính xác qua domain method
     */
    public function test_delivery_status_transitions_and_order_status_sync(): void
    {
        $order = new Order();
        $order->status = \App\Enums\OrderStatus::PENDING; // 0: Chờ xử lý
        $order->delivery_status = Order::DELIVERY_WAITING;

        // 1. Khi shipper nhận hàng: gọi trực tiếp logic nghiệp vụ applyDeliveryStatusTransition
        $order->applyDeliveryStatusTransition(Order::DELIVERY_PICKED_UP);
        $this->assertEquals(\App\Enums\OrderStatus::PROCESSING, $order->status);
        $this->assertEquals(1, $order->status);
        $this->assertNotEquals(\App\Enums\OrderStatus::CANCELLED, $order->status);

        // 2. Khi đang giao hàng: status giữ PROCESSING (1)
        $order->applyDeliveryStatusTransition(Order::DELIVERY_DELIVERING);
        $this->assertEquals(\App\Enums\OrderStatus::PROCESSING, $order->status);

        // 3. Khi đã giao thành công: status PHẢI là COMPLETED (2), tuyệt đối KHÔNG ĐƯỢC là CANCELLED (3)
        $order->applyDeliveryStatusTransition(Order::DELIVERY_DELIVERED);
        $this->assertEquals(\App\Enums\OrderStatus::COMPLETED, $order->status);
        $this->assertEquals(2, $order->status);
        $this->assertNotEquals(3, $order->status);
        $this->assertNotEquals(\App\Enums\OrderStatus::CANCELLED, $order->status);

        // 4. Chặn quay ngược trạng thái từ đã giao về các bước trước (ném DomainException)
        $this->expectException(\DomainException::class);
        $order->applyDeliveryStatusTransition(Order::DELIVERY_WAITING);
    }

    /**
     * Kiểm tra đơn hàng đã hủy thì tuyệt đối không thể cập nhật tiến trình giao hàng
     */
    public function test_cancelled_order_cannot_update_delivery(): void
    {
        $order = new Order();
        $order->status = \App\Enums\OrderStatus::CANCELLED; // 3: Đã hủy
        $order->delivery_status = Order::DELIVERY_WAITING;

        $this->expectException(\DomainException::class);
        $order->applyDeliveryStatusTransition(Order::DELIVERY_DELIVERED);
    }

    /**
     * Kiểm tra quy trình xử lý giao hàng thất bại (failed) và điều phối lại
     */
    public function test_failed_delivery_workflow_and_redispatch(): void
    {
        $order = new Order();
        $order->status = \App\Enums\OrderStatus::PROCESSING;
        $order->delivery_status = Order::DELIVERY_DELIVERING;

        // 1. Chuyển sang failed mà không có ghi chú -> ném DomainException
        try {
            $order->applyDeliveryStatusTransition(Order::DELIVERY_FAILED, '');
            $this->fail('Expected DomainException was not thrown for missing note');
        } catch (\DomainException $e) {
            $this->assertStringContainsString('lý do giao hàng thất bại', $e->getMessage());
        }

        // 2. Chuyển sang failed có lý do -> thành công, status giữ nguyên PROCESSING (để nhân viên xử lý tiếp)
        $order->applyDeliveryStatusTransition(Order::DELIVERY_FAILED, 'Khách thuê bao không liên lạc được');
        $this->assertEquals(Order::DELIVERY_FAILED, $order->delivery_status);
        $this->assertEquals(\App\Enums\OrderStatus::PROCESSING, $order->status);
        $this->assertEquals('Khách thuê bao không liên lạc được', $order->delivery_note);

        // 3. Điều phối lại (từ failed chuyển về waiting)
        $order->applyDeliveryStatusTransition(Order::DELIVERY_WAITING, 'Đã liên lạc lại được khách, hẹn giao ca chiều');
        $this->assertEquals(Order::DELIVERY_WAITING, $order->delivery_status);
        $this->assertEquals(\App\Enums\OrderStatus::PROCESSING, $order->status);
    }

    /**
     * Kiểm tra logic thanh toán COD và VietQR qua helper isPaid
     */
    public function test_order_is_paid_logic_for_cod_and_vietqr(): void
    {
        // 1. Đơn COD: Chưa giao (status = 1, delivery_status = delivering) -> isPaid = false
        $codOrder = new Order();
        $codOrder->payment_method = 'COD';
        $codOrder->status = \App\Enums\OrderStatus::PROCESSING;
        $codOrder->delivery_status = Order::DELIVERY_DELIVERING;
        $this->assertFalse($codOrder->isPaid());

        // Đơn COD: Đã giao thành công (status = 2, delivery_status = delivered) -> isPaid = true
        $codOrder->delivery_status = Order::DELIVERY_DELIVERED;
        $codOrder->status = \App\Enums\OrderStatus::COMPLETED;
        $this->assertTrue($codOrder->isPaid());

        // 2. Đơn VietQR: Mới tạo (status = 0) dù đã quét mã -> isPaid = false
        $vietqrOrder = new Order();
        $vietqrOrder->payment_method = 'VietQR';
        $vietqrOrder->status = \App\Enums\OrderStatus::PENDING;
        $this->assertFalse($vietqrOrder->isPaid());

        // Đơn VietQR: Webhook xác nhận tiền về (status = 1, payment_status = paid) -> isPaid = true
        $vietqrOrder->status = \App\Enums\OrderStatus::PROCESSING;
        $vietqrOrder->payment_status = Order::PAYMENT_PAID;
        $this->assertTrue($vietqrOrder->isPaid());
    }
}
