# 🍽️ CIVILIZE Restaurant Management & POS System
> **Hệ Thống Quản Lý Nhà Hàng, Đặt Bàn, Gọi Món QR Tại Bàn & POS Đa Nền Tảng**  
> *Đồ án tốt nghiệp / Đồ án chuyên ngành Công Nghệ Thông Tin*

---

## 📌 Giới Thiệu Dự Án
**CIVILIZE Restaurant** là giải pháp phần mềm quản lý nhà hàng toàn diện được xây dựng trên nền tảng **Laravel 11** và **PHP 8.2**, kết hợp giao diện hiện đại **Blade + TailwindCSS**. Hệ thống tối ưu hóa quy trình vận hành từ khâu khách hàng trải nghiệm (gọi món tại bàn qua mã QR, đặt bàn trước, giao hàng tận nơi) đến nghiệp vụ của nhân viên phục vụ, thu ngân, nhà bếp và ban quản lý.

---

## 🚀 Các Tính Năng Nổi Bật

### 1. Phân Hệ Khách Hàng (Customer Experience)
* **Quét mã QR tại bàn (Dine-in QR Ordering):** Khách hàng quét mã QR trên bàn để xem thực đơn và gọi món trực tiếp vào bếp mà không cần cài đặt ứng dụng.
* **Đặt bàn trực tuyến (Table Reservation):** Lựa chọn số lượng khách, ngày giờ, phòng VIP và ghi chú bữa tiệc.
* **Giao hàng tận nơi (Online Delivery):** Tính toán khoảng cách giao hàng, phí ship tự động (dưới 500k áp dụng 25k, trên 500k miễn phí vận chuyển trong bán kính 15km) và giỏ hàng thông minh.
* **Cổng thanh toán điện tử:** Tích hợp chuyển khoản tự động **VietQR** (đối soát biến động số dư tự động qua Webhook) và thanh toán Tiền mặt khi nhận hàng (COD).
* **Trợ lý ảo ẩm thực (AI Chatbot):** Tích hợp Google Gemini AI hỗ trợ tư vấn món ăn, giải đáp thực đơn 24/7.

### 2. Phân Hệ Quản Trị & Điểm Bán Hàng (Admin & POS)
* **Màn hình POS gọi món tại bàn:** Bố cục sơ đồ bàn theo tầng, hỗ trợ gộp bàn, chuyển bàn và tách bàn linh hoạt.
* **Màn hình Bếp (Kitchen Order Ticket - KOT):** Nhận thông báo món mới theo thời gian thực (realtime notification), cập nhật trạng thái chế biến (*chờ bếp nhận -> đang nấu -> đã lên bàn*).
* **Báo cáo & Thống kê doanh thu:** Xuất báo cáo dữ liệu định dạng Microsoft Excel, thống kê doanh thu theo ngày/tháng/năm.
* **Quản trị người dùng & Phân quyền:** Phân tầng 2 lớp (*Admin cấp cao* và *Nhân viên phục vụ/thu ngân*).

---

## 🛡️ Thiết Kế Bảo Mật & Toàn Vẹn Dữ Liệu (Security & Concurrency Architecture)

* **Kiểm soát đồng thời (Pessimistic Locking & Transactions):**
  * Sử dụng `DB::transaction` kết hợp `lockForUpdate()` trong các thao tác thêm món, hủy món và thanh toán hóa đơn.
  * Ngăn ngừa hoàn toàn hiện tượng *Race Condition* khi nhiều nhân viên hoặc khách cùng thao tác trên một bàn.
* **Nhật ký kiểm toán món ăn (`OrderItemLog`):**
  * Mọi hành động thêm số lượng, giảm số lượng hoặc hủy món đều được ghi vết chi tiết (người thực hiện, thời gian, lý do, số lượng cũ/mới, trạng thái bếp).
  * **Hủy mềm (Soft cancellation):** Khi món đã gửi bếp chế biến, hệ thống hủy mềm và bảo toàn lịch sử thay vì xóa cứng khỏi cơ sở dữ liệu.
* **Kiến trúc thanh toán đa môi trường (`PAYMENT_MODE`):**
  * `PAYMENT_MODE=demo`: Kích hoạt **Payment Mock Simulator** (Webhook giả lập VietQR) giúp hội đồng chấm thi kiểm thử toàn bộ luồng thanh toán mà không phát sinh giao dịch tiền mặt.
  * `PAYMENT_MODE=production`: Tự động vô hiệu hóa toàn bộ endpoint giả lập (HTTP 404), bắt buộc xác thực token webhook qua HTTP Header (`Authorization: Apikey` / `secure-token`) và đối soát số tiền chuyển khoản >= tổng hóa đơn.
* **Phòng chống các lỗ hổng Web phổ biến:**
  * Toàn bộ thao tác thay đổi trạng thái quản trị chuyển sang phương thức `POST` có mã bảo vệ `@csrf`.
  * Tái tạo Session ID (`$request->session()->regenerate()`) khi đăng nhập để chống tấn công *Session Fixation*.
  * Chặn đứng lỗ hổng bypass mật khẩu qua số điện thoại và ngăn chặn dò quét OTP brute-force.

---

## 🧪 Kiểm Thử Tự Động (Automated Testing)

Dự án tích hợp sẵn bộ kiểm thử chức năng (Feature Tests & Unit Tests) với **100% Test Case đạt chuẩn (30 passed / 93 assertions)**:

```bash
php artisan test
```

**Các nhóm kiểm thử chính:**
* **Chatbot Security Audit:** Chống rò rỉ đơn hàng, chặn rò rỉ thông tin cá nhân khách (PII masking), chống đầu độc hồ sơ AI (profile poisoning), chặn auto-create booking khi thiếu xác nhận rõ ràng, xác thực lịch đặt bàn và kiểm soát bộ nhớ hội thoại.
* **Security & Payment:** Phân quyền nghiêm ngặt giữa Admin/Nhân viên/Khách hàng, bảo vệ Webhook thanh toán VietQR (chặn token mặc định, chặn giả mạo simulator ở production, chặn số tiền thiếu hoặc thanh toán lặp), chuyển toàn bộ 33 route admin sang POST CSRF (chặn hoàn toàn GET 405), chặn xóa giỏ hàng bằng GET 405.
* **Order & Inventory:** Kiểm tra trừ tồn kho với DB Transaction và Pessimistic Locking (`lockForUpdate`), tự động tính phí giao hàng và miễn phí theo ngưỡng.
* **Notification Segregation:** Phân tách thông báo bảo mật giữa 3 vai trò Admin, Nhân viên và Khách hàng.
* **Excel Reports:** Kiểm thử xuất dữ liệu chuẩn định dạng cho 7 phân hệ báo cáo.

---

## ⚙️ Hướng Dẫn Cài Đặt & Khởi Chạy

### 1. Yêu Cầu Môi Trường
* **PHP:** >= 8.2
* **MySQL:** >= 8.0 (hoặc MariaDB >= 10.4)
* **Composer:** >= 2.0

### 2. Các Bước Cài Đặt
```bash
# 1. Clone repository
git clone https://github.com/Thooooo1/dev_restaurant_laravel.git
cd dev_restaurant_laravel

# 2. Cài đặt các thư viện phụ thuộc
composer install

# 3. Tạo file cấu hình môi trường
cp .env.example .env

# 4. Sinh APP_KEY
php artisan key:generate

# 5. Cấu hình cơ sở dữ liệu trong file .env và chạy migration
php artisan migrate

# 6. Khởi chạy máy chủ phát triển
php artisan serve
```

---

## 🔑 Tài Khoản Thử Nghiệm (Demo Accounts)

Hệ thống tích hợp sẵn nút bấm **1-Click Tự Động Điền** tại màn hình đăng nhập:

| Vai trò | Tên đăng nhập | Mật khẩu | Quyền hạn truy cập |
| :--- | :--- | :--- | :--- |
| **Quản trị viên (Admin)** | `admin1` | `123456` | Toàn quyền quản trị hệ thống, báo cáo, sản phẩm, nhân sự |
| **Nhân viên (Employee)** | `nhanvien1` | `123456` | Màn hình POS gọi món tại bàn, sơ đồ bàn, phục vụ |
| **Khách hàng (Customer)** | `khachhang1` | `123456` | Đặt món giao tận nơi, đặt bàn, theo dõi đơn hàng |

---

## 📄 Bản Quyền & Giấy Phép
Dự án được phát triển phục vụ mục đích học tập và bảo vệ đồ án tốt nghiệp chuyên ngành Công Nghệ Thông Tin.
