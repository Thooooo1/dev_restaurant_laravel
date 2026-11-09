# CIVILIZE Restaurant Management & POS System
> He Thong Quan Ly Nha Hang, Dat Ban, Goi Mon QR Tai Ban & POS Da Nen Tang  
> Do an tot nghiep / Do an chuyen nganh Cong Nghe Thong Tin

---

## 1. GIOI THIEU DU AN
CIVILIZE Restaurant la giai phap phan mem quan ly nha hang toan dien duoc xay dung tren nen tang **Laravel 11** va **PHP 8.2**, ket hop cong nghe ket xuat hien dai **Blade + Tailwind CSS** dong goi cuc bo qua **Vite**. He thong toi uu hoa toan dien quy trinh van hanh tu khau thuc khach trai nghiem (quet ma QR tai ban, dat ban truoc, giao hang tan noi) den nghiep vu chuyen sau cua nhan vien phuc vu, thu ngan, nha bep va ban quan tri.

Tai lieu thiet ke va bao ve chuyen sau:
* Bao cao kien truc va so do he thong: [docs/BAO_CAO_KIEN_TRUC_VA_SO_DO_HE_THONG.md](docs/BAO_CAO_KIEN_TRUC_VA_SO_DO_HE_THONG.md)
* Kich ban thuyet trinh demo va bo cau hoi phan bien: [docs/KICH_BAN_DEMO_BAO_VE_VA_HOI_DAP.md](docs/KICH_BAN_DEMO_BAO_VE_VA_HOI_DAP.md)

---

## 2. CAC TINH NANG NOI BAT

### Phuc Vu Khach Hang (Customer Experience)
* **Quet ma QR tai ban (Dine-in QR Ordering):** Khach hang quet ma QR co dinh tai ban de xem thuc don, them mon vao gio hang va goi mon truc tiep xuong bep ma khong can cai dat bat ky ung dung nao.
* **Goi nhan vien phuc vu tai ban:** Tich hop hop thoai goi phuc vu truc quan (them da, khan lanh, bat dia, cham nuoc, goi thanh toan), tu dong xoa canh bao khi da chon ban va phat tin hieu realtime cho thu ngan.
* **Dat ban truc tuyen (Table Reservation):** Chon so luong khach, ngay gio, phong VIP va ghi chu bua tiec.
* **Giao hang tan noi (Online Delivery & Timeline Tracking):** Tinh toan phi ship tu dong dua tren nguong hoa don qua `PricingService`. Theo doi tien do don hang qua Timeline 4 buoc (Da dat -> Dang chuan bi -> Shipper da lay hang -> Dang giao -> Hoan tat) kem nut goi dien va Zalo tai xe truc tiep.
* **Cong thanh toan dien tu tu dong:** Tich hop chuyen khoan **VietQR** (doi soat tu dong qua Webhook voi token bao mat) va thanh toan Tien mat.
* **Tro ly ao am thuc AI (Google Gemini):** Tu van mon an dua tren so thich, giai dap thuc don, tra cuu don hang an toan voi co che che giau thong tin ca nhan (PII Masking).

### Quan Tri & Diem Ban Hang (Admin & POS)
* **Man hinh POS goi mon tai ban:** So do ban truc quan theo tang, ho tro gop ban, chuyen ban, in hoa don tam tinh va tach ban linh hoat.
* **Man hinh Bep (Kitchen Order Ticket - KOT):** Dong bo don mon moi realtime qua polling moi 5 giay, cap nhat trang thai che bien (Cho che bien -> Dang nau -> Da len ban).
* **Nhat ky kiem toan mon an (OrderItemLog):** Co che huy mem (Soft Cancellation) khi mon da vao bep, luu vet chi tiet nguoi huy va ly do huy de chong gian lan noi bo.
* **Quan tri giao hang tinh gon:** Nhan vien nha hang truc tiep gan ten va so dien thoai shipper ngoai ma khong can phuc tap hoa voi Role Shipper rieng.
* **Bao cao & Thong ke doanh thu:** Xuat bao cao dinh dang Microsoft Excel, thong ke doanh thu theo ngay/thang/nam va phan tich mon an ban chay.

---

## 3. KIEN TRUC VA TOAN VEN DU LIEU (ARCHITECTURE & SECURITY)

* **Kiem soat dong thoi (Pessimistic Locking & ACID Transactions):**
  * Su dung `DB::transaction()` ket hop `lockForUpdate()` trong cac thao tac dat hang, huy mon, cap nhat ton kho va xac nhan thanh toan.
  * Ngan ngua hoan toan hien tuong *Race Condition* khi nhieu nguoi cung thao tac tren mot ban an hoac mot mat hang ton kho.
* **Dich vu tinh toan tap trung (PricingService & PaymentMethod Enum):**
  * Toan bo logic tinh phi ship, tong tien, chiet khau ban tiec duoc quy ve `app/Services/PricingService.php`.
  * Phuong thuc thanh toan duoc dinh nghia nhat quan qua `app/Enums/PaymentMethod.php`.
* **Kien truc thanh toan da moi truong (PAYMENT_MODE):**
  * `PAYMENT_MODE=demo`: Kich hoat bo mo phong Webhook VietQR giup hoi dong kiem thu luong thanh toan tu dong ma khong ton tien that.
  * `PAYMENT_MODE=production`: Tu dong chan toan bo endpoint mo phong (HTTP 404), bat buoc xac thuc token bao mat qua HTTP Header va doi soat so tien giao dich.
* **Dong goi tai nguyen cuc bo 100% (Vite Offline Bundle):**
  * CSS (Tailwind), Font Awesome 6 va webfonts duoc build san trong `public/build`.
  * He thong hoat dong on dinh ca khi khong co ket noi Internet ngoai, kem co che Fallback CDN tu dong.
* **Phong thu chieu sau (Defense-in-Depth):**
  * Chuyen doi toan bo cac route quan tri sang phuong thuc `POST` kem `@csrf`.
  * Chot chan lo hong Open Redirect qua co che Safe Local Redirection.
  * Tu dong lam moi token CSRF cho Chatbot qua endpoint `/refresh-csrf`.
  * Thu hep Proxy tin cay qua bien moi truong `TRUSTED_PROXIES`.

---

## 4. KIEM THU TU DONG & CI/CD (AUTOMATED TESTING & CI/CD)

Du an tich hop bo kiem thu tu dong (Feature Tests & Unit Tests) voi **100% Test Case dat tieu chuan (35 tests / 116 assertions)**:

```bash
# Chay toan bo test suite tren moi truong may chu / may phat trien
php artisan test
```

### Cac Nhom Kiem Thu Chinh:
1. **Security & Payment (`SecurityAndPaymentTest`):** Phan quyen phan tang Admin/Nhan vien/Khach hang, bao ve Webhook VietQR, chan toan bo 33 route admin khoi phuong thuc GET (chong 405), kiem tra chan Open Redirect va Safe Logout.
2. **Chatbot Security Audit (`ChatbotSecurityTest`):** Chong ro ri PII khach hang, chan prompt injection, xac thuc 2 yeu to khi tra cuu don hang, kiem soat bo nho hoi thoai.
3. **Delivery & Pricing (`DeliveryAndPricingTest`):** Kiem tra do chinh xac cua `PricingService`, ap dung chiet khau ban tiec 10%, tinh phi ship tu dong theo nguong hoa don, xac thuc `PaymentMethod` Enum va nhan trang thai giao hang 4 buoc.
4. **Order & Inventory:** Kiem tra tru ton kho an toan voi Pessimistic Locking.
5. **Excel Export Reports:** Kiem tra xuat du lieu cho cac phan he bao cao doanh thu va xuat nhap kho.

### Tich Hop Lien Tuc (CI/CD):
Quy trinh GitHub Actions tu dong duoc cau hinh tai `.github/workflows/tests.yml`: Khoi tao moi truong ao hoa MySQL 8.0, chay migration va kiem thu toan bo ma nguon moi khi co ma duoc day len repository.

---

## 5. HUONG DAN CAI DAT VA KHOI CHAY

### 1. Yeu Cau Moi Truong
* PHP >= 8.2
* MySQL >= 8.0 (hoac MariaDB >= 10.4)
* Composer >= 2.0
* Node.js >= 18.0 & npm >= 9.0

### 2. Cac Buoc Cai Dat
```bash
# 1. Clone repository
git clone https://github.com/Thooooo1/dev_restaurant_laravel.git
cd dev_restaurant_laravel

# 2. Cai dat cac thu vien PHP
composer install

# 3. Cai dat cac thu vien JavaScript va build assets
npm install
npm run build

# 4. Tao file cau hinh moi truong
cp .env.example .env

# 5. Sinh APP_KEY
php artisan key:generate

# 6. Cau hinh thong so ket noi co so du lieu trong file .env va chay migration
php artisan migrate

# 7. Khoi chay may chu phat trien
php artisan serve
```

---

## 6. TAI KHOAN THU NGHIEM (DEMO ACCOUNTS)

He thong tich hop san nut bam **1-Click Tu Dong Dien** tai man hinh dang nhap de phuc vu kiem thu truc quan:

| Vai tro | Ten dang nhap | Mat khau | Quyen han truy cap |
| :--- | :--- | :--- | :--- |
| **Quan tri vien (Admin)** | `admin1` | `123456` | Toan quyen he thong, bao cao doanh thu, san pham, nhan su |
| **Nhan vien (Employee)** | `nhanvien1` | `123456` | Man hinh POS goi mon tai ban, so do ban, bep, phuc vu |
| **Khach hang (Customer)** | `khachhang1` | `123456` | Dat mon giao tan noi, dat ban, theo doi tien do don hang |

---

## 7. BAN QUYEN & GIAY PHEP
Du an duoc phat trien phuc vu muc dich hoc tap va bao ve do an tot nghiep chuyen nganh Cong Nghe Thong Tin.
All rights reserved.
