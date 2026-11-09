# BAO CAO KIEN TRUC VA SO DO HE THONG THIET KE
## HE THONG QUAN LY NHA HANG, POS VA GOI MON QR CIVILIZE RESTAURANT

---

### PHAN 1: TONG QUAN KIEN TRUC HE THONG (SYSTEM ARCHITECTURE OVERVIEW)

He thong CIVILIZE Restaurant duoc xay dung theo mo hinh **Kien truc Da tang Nguyen khoi hien dai (Modern Modular Monolith MVC)** tren nen tang **Laravel 11**, **PHP 8.2**, ket hop cong nghe ket xuat phia may chu (Server-Side Rendering voi Blade Templates), xu ly dong bo tai nguyen cuc bo (Local Asset Bundling voi Vite, Tailwind CSS va Font Awesome 6) va co che trao doi du lieu thoi gian thuc nhe (Lightweight Realtime Polling & Asynchronous REST API).

```mermaid
graph TD
    subgraph ClientLayer ["1. Tang Khach Hang & Nguoi Dung (Client / Presentation Layer)"]
        MobileClient["Khach Quet QR Tai Ban (Mobile Browser)"]
        WebClient["Khach Hang Dat Truc Tuyen (Desktop / Mobile Web)"]
        StaffClient["Nhan Vien Phuc Vu / Thu Ngan (POS Tablet / PC)"]
        AdminClient["Quan Tri Vien (Admin Dashboard Web)"]
    end

    subgraph GatewayLayer ["2. Tang Dinh Tuyen & Bao Ve (Security & Gateway Layer)"]
        WebRoutes["Web Router & REST API Routes"]
        RateLimiter["IP Rate Limiter & Throttle Protection"]
        CsrfFilter["CSRF Protection & Token Regenerator"]
        AuthMiddleware["Role & Session Access Control Middleware"]
    end

    subgraph ServiceLayer ["3. Tang Nghiep Vu & Dich Vu (Application & Domain Services)"]
        PricingSvc["PricingService (Tinh Phi Ship, Tong Tien, Uu Dai)"]
        PaymentEnum["PaymentMethod Enum (Tien Mat, VietQR, The POS)"]
        ChatbotCore["ChatbotService (Google Gemini AI Engine)"]
        ChatbotKB["ChatbotKnowledgeBase (Semantic Restaurant Context)"]
        ChatbotMem["ChatbotMemoryManager (Session Conversation History)"]
        AuditLogger["OrderItemLog Engine (Kiem Toan Mon & Huy Mem)"]
    end

    subgraph DataLayer ["4. Tang Du Lieu & Luu Tru (Data & Persistence Layer)"]
        MySQL[("Co So Du Lieu Quan He MySQL 8.0 (InnoDb, Foreign Keys, Indexes)")]
        LocalCache["File / Session Storage & settings.json"]
        ViteAssets["Public Build Asset Bundles (CSS / JS / Webfonts)"]
    end

    ClientLayer --> GatewayLayer
    GatewayLayer --> ServiceLayer
    ServiceLayer --> DataLayer
```

---

### PHAN 2: SO DO USE CASE TONG THE VA PHAN RA CHI TIET

#### 1. So Do Use Case Tong The (General System Use Cases)

```mermaid
graph LR
    KhachHang["Khach Hang (Customer)"]
    NhanVien["Nhan Vien Phuc Vu / Thu Ngan (Staff)"]
    QuanTri["Quan Tri Vien (Admin)"]

    subgraph CoreUseCases ["Cac Nhom Use Case Chinh Cua He Thong"]
        UC1["1. Quet ma QR xem menu va goi mon tai ban"]
        UC2["2. Dat ban tiec truc tuyen"]
        UC3["3. Dat mon giao tan noi & Theo doi tien do"]
        UC4["4. Thanh toan chuyen khoan tu dong VietQR"]
        UC5["5. Tu van thuc don voi Tro ly ao AI"]
        UC6["6. Goi nhan vien phuc vu truc tiep tu ban"]
        UC7["7. Quan ly so do ban, them/huy mon, tach/gop ban (POS)"]
        UC8["8. Dieu phoi don bep (KOT - Kitchen Order Ticket)"]
        UC9["9. Gan thong tin van chuyen & cap nhat giao hang"]
        UC10["10. Quan ly san pham, danh muc, nhap kho nguyen lieu"]
        UC11["11. Quan tri nhan su, phan quyen & thong ke doanh thu"]
    end

    KhachHang --> UC1
    KhachHang --> UC2
    KhachHang --> UC3
    KhachHang --> UC4
    KhachHang --> UC5
    KhachHang --> UC6

    NhanVien --> UC7
    NhanVien --> UC8
    NhanVien --> UC9
    NhanVien --> UC4

    QuanTri --> UC7
    QuanTri --> UC8
    QuanTri --> UC9
    QuanTri --> UC10
    QuanTri --> UC11
```

#### 2. Phan Ra Use Case Theo Tung Tac Nhan (Actor Breakdown)

* **Tac nhan Khach hang tai ban:**
  * Quet ma QR doc tai cho (chua dinh danh ban, tang).
  * Xem thuc don danh muc, chi tiet nguyen lieu va gia niem yet.
  * Them mon vao gio hang tai ban, ap dung uu dai ban tiec.
  * Gui yeu cau goi mon vao hang doi bep.
  * Gui tin hieu yeu cau phuc vu (them da, khan lanh, bat dia, cham nuoc, goi thanh toan).
  * Quet ma VietQR dong chua noi dung thanh toan de tra tien.
* **Tac nhan Khach hang dat truc tuyen:**
  * Dang ky / Dang nhap tai khoan xac thuc.
  * Dat ban truoc (chon so luong nguoi, gio den, phong VIP, ghi chu).
  * Chon mon giao tan noi, he thong tu dong tinh phi van chuyen dua theo ban kinh va nguong mien phi hoa don tu `settings.json`.
  * Theo doi timeline hanh trinh don hang (Da dat -> Dang chuan bi -> Da ban giao shipper -> Dang giao -> Hoan tat) va nut lien he goi dien / Zalo shipper truc tiep.
* **Tac nhan Nhan vien:**
  * Xem truc quan trang thai ban (Trong, Dang su dung, Co khach dat truoc).
  * Thao tac chuyen ban, gop ban, in hoa don tam tinh.
  * Tiep nhan mon moi tai man hinh Bep KOT, doi trang thai mon (Cho che bien -> Dang nau -> Da len ban).
  * Huy mon an kem ly do ghi nhan vao `order_item_logs` (Soft Cancellation).
  * Nhap thong tin don vi van chuyen ngoai (ten shipper, so dien thoai, ghi chu) va bam chuyen doi tien do giao hang.
* **Tac nhan Quan tri vien:**
  * Toan quyen thao tac phan he POS va bep.
  * Thiet lap tham so he thong trong `settings.json` (phi ship, nguong freeship, ngan hang VietQR, hotline nha hang).
  * Quan ly tai khoan nhan vien, phan quyen truy cap.
  * Quan ly danh muc, san pham, phieu nhap kho, ton kho nguyen lieu.
  * Xem bao cao doanh thu, xuat file Excel thong ke theo moc thoi gian.

---

### PHAN 3: SO DO CO SO DU LIEU QUAN HE (ENTITY RELATIONSHIP DIAGRAM - ERD)

Co so du lieu thiet ke chuan hoa theo dang chuan 3 (3NF), ho tro rang buoc toan ven khoa ngoai (Foreign Keys), khoa duy nhat (Unique) va chi muc toi uu hoa truy van (Indexes):

```mermaid
erDiagram
    USERS ||--o{ ORDERS : "places"
    USERS ||--o{ BOOKINGS : "reserves"
    USERS ||--o{ GOODS_RECEIPTS : "creates"
    USERS ||--o{ ORDER_ITEM_LOGS : "logs_action"

    FLOORS ||--o{ TABLES : "contains"
    TABLES ||--o{ ORDERS : "serves"
    TABLES ||--o{ SERVICE_CALLS : "requests"

    CATEGORIES ||--o{ PRODUCTS : "groups"
    BRANDS ||--o{ PRODUCTS : "manufactures"
    PRODUCTS ||--o{ ORDER_DETAILS : "ordered_in"
    PRODUCTS ||--o{ GOODS_RECEIPT_DETAILS : "received_in"

    ORDERS ||--|{ ORDER_DETAILS : "includes"
    ORDERS ||--o{ ORDER_ITEM_LOGS : "tracks_changes"
    ORDERS ||--o{ SERVICE_CALLS : "associated_with"

    GOODS_RECEIPTS ||--|{ GOODS_RECEIPT_DETAILS : "specifies"

    USERS {
        bigint id PK
        string name
        string email UK
        string phone
        string username UK
        string password
        int roles "1: Admin, 2: Staff, 3: Customer"
        int status
        timestamp created_at
    }

    FLOORS {
        bigint id PK
        string name
        int order
        int status
    }

    TABLES {
        bigint id PK
        bigint floor_id FK
        string name
        int capacity
        int status "0: Empty, 1: Occupied, 2: Reserved"
        string qr_code_path
    }

    CATEGORIES {
        bigint id PK
        string name
        string slug UK
        bigint parent_id
        int status
    }

    BRANDS {
        bigint id PK
        string name
        string slug UK
        int status
    }

    PRODUCTS {
        bigint id PK
        bigint category_id FK
        bigint brand_id FK
        string name
        string slug UK
        decimal price
        decimal pricesale
        int qty "So luong ton kho"
        string image
        int status
    }

    ORDERS {
        bigint id PK
        bigint user_id FK
        bigint table_id FK "Nullable: don online"
        string name "Ten nguoi nhan"
        string phone
        string email
        string address
        decimal total_money
        decimal shipping_fee
        int payment_method "1: Cash, 2: VietQR, 3: POS"
        int status "0: Pending, 1: Processing, 2: Delivering, 3: Completed"
        string delivery_status "waiting, picked_up, delivering, delivered, failed"
        string shipper_name
        string shipper_phone
        timestamp picked_up_at
        timestamp delivered_at
        string delivery_note
        timestamp created_at
    }

    ORDER_DETAILS {
        bigint id PK
        bigint order_id FK
        bigint product_id FK
        int qty
        decimal price
        decimal discount
        decimal amount
        string kitchen_status "pending, cooking, ready, served"
        int is_cancelled "0: Normal, 1: Cancelled"
        string cancel_reason
    }

    ORDER_ITEM_LOGS {
        bigint id PK
        bigint order_id FK
        bigint product_id FK
        bigint user_id FK "Nguoi thuc hien"
        string action "add, update_qty, cancel, restore"
        int old_qty
        int new_qty
        string reason
        timestamp created_at
    }

    BOOKINGS {
        bigint id PK
        bigint user_id FK
        string customer_name
        string phone
        int people
        date booking_date
        time booking_time
        string room_type
        string note
        int status "0: Cho duyet, 1: Da nhan ban, 2: Huy"
        timestamp created_at
    }

    SERVICE_CALLS {
        bigint id PK
        bigint table_id FK
        bigint order_id FK
        string call_type "ice, tissue, water, payment, other"
        string note
        int status "0: Cho phuc vu, 1: Da xong"
        timestamp created_at
    }

    NOTIFICATIONS {
        bigint id PK
        string type
        string notifiable_type
        bigint notifiable_id
        text data
        timestamp read_at
        timestamp created_at
    }

    CHATBOT_MESSAGES {
        bigint id PK
        string session_id
        bigint user_id FK
        string role "user, model"
        text content
        timestamp created_at
    }
```

---

### PHAN 4: SO DO TUAN TU CAC NGHIEP VU TRONG YEU (SEQUENCE DIAGRAMS)

#### 1. Quy Trinh Goi Mon Tai Ban Bang Ma QR (Dine-in QR Ordering Flow)

```mermaid
sequenceDiagram
    autonumber
    actor C as Khach Hang (Dine-in)
    participant B as Trinh Duyet Web (Mobile)
    participant R as Router / TableOrderController
    participant PS as PricingService
    participant DB as Co So Du Lieu (MySQL)
    participant K as Man Hinh Bep (Kitchen Display)

    C->>B: Quet ma QR dan tren mat ban
    B->>R: GET /dat-mon-tai-ban/{table_id}
    R->>R: Luu session (is_table_order = true, table_id)
    R->>DB: Truy van danh sach thuc don & trang thai ban
    R-->>B: Tra ve giao dien thuc don tai ban
    
    C->>B: Chon mon & tang/giam so luong
    B->>PS: Goi POST /cart/add (Cap nhat gio hang)
    PS->>PS: calculateCartSummary() (Ap dung giam gia 10% tai ban)
    PS-->>B: Tra ve json gio hang moi nhat
    
    C->>B: Nhan "Gui Yeu Cau Goi Mon"
    B->>R: POST /cart/checkout-table
    R->>DB: DB::beginTransaction()
    R->>DB: lockForUpdate() kiem tra ton kho & tao Order (status=Pending)
    R->>DB: Tao cac ban ghi OrderDetail (kitchen_status='pending')
    R->>DB: Ghi log khoi tao vao OrderItemLog
    R->>DB: DB::commit()
    R-->>B: Thong bao dat mon thanh cong
    
    loop Polling moi 5 giay
        K->>DB: GET /admin/kitchen/orders (Kiem tra mon moi)
        DB-->>K: Tra ve danh sach mon can che bien
    end
```

#### 2. Quy Trinh Thanh Toan Tu Dong Chuyen Khoan VietQR (VietQR Automated Payment)

```mermaid
sequenceDiagram
    autonumber
    actor C as Khach Hang
    participant App as App Ngan Hang (Napas 247)
    participant Web as Giao Dien Hoa Don (Web)
    participant WH as Webhook Listener (/api/payment/webhook)
    participant DB as Co So Du Lieu (MySQL)
    actor S as Thu Ngan / Nhan Vien (Admin POS)

    Web->>Web: Tao ma VietQR chua cu phap: ORD + {order_id}
    C->>App: Quet ma VietQR & Xac nhan chuyen khoan
    App->>App: Chuyen tien qua mang luoi Napas 247 den tai khoan nha hang
    
    alt Truong hop Production (Tich hop Casso / SePay / Ngan Hang)
        App->>WH: POST /api/payment/webhook (Header: Authorization: Apikey)
        WH->>WH: Kiem tra Secure Token trong Header
        WH->>WH: Trich xuat ma don tu noi dung giao dich (Regex: ORD\d+)
        WH->>DB: DB::beginTransaction()
        WH->>DB: lockForUpdate() khoa dong Order theo ID
        WH->>DB: Doi chieu so tien nhan >= Order.total_money
        WH->>DB: Cap nhat Order (payment_method=2, status=Completed)
        WH->>DB: Cap nhat Table (status=0 - Trong) neu la don tai ban
        WH->>DB: Tao ban ghi Notification (type='payment_received')
        WH->>DB: DB::commit()
        WH-->>App: HTTP 200 OK
    else Truong hop Hoi Dong Kiem Thu (PAYMENT_MODE=demo)
        S->>WH: POST /api/payment/mock-webhook (Nhan nut Gia lap chuyen khoan)
        WH->>DB: Kiem tra config(app.payment_mode) === 'demo'
        WH->>DB: Xu ly cap nhat tuong tu luong that
        WH-->>S: Thong bao mo phong thanh toan thanh cong
    end
    
    loop Realtime Polling (Moi 5s)
        S->>DB: GET /admin/notifications/check-new
        DB-->>S: Cap nhat huy hieu chuong thong bao & am thanh giao dich
    end
```

#### 3. Quy Trinh Dieu Phoi Giao Hang Khong Can Role Shipper Rieng

```mermaid
sequenceDiagram
    autonumber
    actor C as Khach Hang (Online)
    actor S as Nhan Vien Nha Hang (Staff)
    actor Sh as Nguoi Giao Hang Ngoai (External Shipper)
    participant Web as He Thong Web Restaurant
    participant DB as Co So Du Lieu (MySQL)

    C->>Web: Dat don giao hang thanh cong (delivery_status='waiting')
    S->>Web: Mo chi tiet don hang trong /admin/order
    S->>Sh: Goi dien / Nhan tin cho don vi shipper ngoai
    Sh-->>S: Nhan don va bao thoi gian den lay
    
    S->>Web: Nhap ten shipper, SDT vao form va bam "Shipper da lay hang"
    Web->>DB: UPDATE orders SET delivery_status='picked_up', shipper_name, shipper_phone, picked_up_at=NOW()
    
    S->>Web: Bam "Dang giao hang"
    Web->>DB: UPDATE orders SET delivery_status='delivering'
    
    C->>Web: Mo trang xem chi tiet /don-hang/{id}
    Web->>DB: SELECT * FROM orders WHERE id={id}
    DB-->>Web: Tra ve thong tin don
    Web-->>C: Hien thi Timeline 4 buoc + Ten shipper + Nut goi Dien / Zalo Sh
    
    Sh->>C: Giao mon an tan noi va nhan tien COD (neu co)
    Sh-->>S: Bao tin don da giao thanh cong
    S->>Web: Bam "Da giao thanh cong"
    Web->>DB: UPDATE orders SET delivery_status='delivered', status=3 (Completed), delivered_at=NOW()
    Web-->>C: Cap nhat Timeline don hoan tat 100%
```

---

### PHAN 5: PHAN TICH CHI TIET KIEN TRUC BAO MAT VA TOAN VEN DU LIEU

He thong ap dung cac tieu chuan phong thu chieu sau (Defense-in-Depth) de bao ve toan ven du lieu va ngan chan cac lo hong web:

1. **Kiem soat Dong thoi & Chong Tranh Chap Du Lieu (Pessimistic Locking & ACID Transactions):**
   * Trong tat ca cac nghiep vu nhay cam: dat hang tru ton kho, xac nhan webhook thanh toan, gop ban, doi trang thai mon, he thong luon boc trong `DB::beginTransaction()` ket hop `lockForUpdate()`.
   * Dam bao neu co nhieu luong cung luc truy cap (vi du 2 nhan vien cung bam thanh toan 1 ban, hoac 2 khach hang cung dat mon cuoi cung trong kho), cac transaction se duoc xep hang tuan tu, triet tieu hoan toan hien tuong Deadlock va Race Condition.

2. **Kiem Toan Mon An & Co Che Huy Mem (Soft Cancellation & Audit Trail):**
   * Khi mon an da duoc gui vao bep che bien, he thong cam tuyet doi lenh `DELETE` vat ly khoi bang `order_details`.
   * Mung huy mon su dung co che danh dau `is_cancelled = 1` kem `cancel_reason`. Dong thoi moi thao tac sua so luong, huy mon deu duoc he thong tu dong ghi vao bang `order_item_logs` luu lai ID nhan vien thao tac, so luong truoc/sau va ly do.

3. **Chong Tan Cong Chuyen Huong Mo (Open Redirect Protection):**
   * Ham xac thuc `AuthController::login()` su dung co che kiem duyet chuoi chuyen huong noi bo:
     ```php
     if ($redirectUrl && str_starts_with($redirectUrl, '/') && !str_starts_with($redirectUrl, '//')) {
         return redirect($redirectUrl);
     }
     return redirect()->route('home');
     ```
   * Ngan chan triet de ke tan cong chen URL ngoai doc hai (vi du: `?redirect=https://evil-phishing.com`) de lua dao nguoi dung sau khi dang nhap.

4. **Co Che Tu Dong Lam Moi Token CSRF Cho Chatbot & SPA:**
   * Thay vi loai bo kiem tra CSRF (lam tang nguy co gia mao yeu cau giua cac trang web), he thong cung cap endpoint bao mat `/refresh-csrf`.
   * Chatbot JavaScript tu dong goi endpoint nay de cap nhat token hop le truoc moi phien gui hoi thoai, giai quyet dut diem loi `419 Page Expired` tren hosting cong khai ma van duy tri lop la chan CSRF tuyet doi.

5. **Thu Hep Danh Sach Proxy Tin Cay (Trusted Proxies Narrowing):**
   * Trong `bootstrap/app.php`, he thong khong su dung cau hinh mac dinh nguy hiem `trustProxies(at: '*')` von cho phep gia mao header `X-Forwarded-For`.
   * He thong su dung bien moi truong `TRUSTED_PROXIES` voi dai IP xac dinh ro rang cua he thong Reverse Proxy / Cloudflare, bao ve do chinh xac cua co che Rate Limiting theo dia chi IP thuc cua client.

6. **Bao Ve Quyen Rieng Tu & An Toan Du Lieu Tro Ly Ao AI (Chatbot Security & Privacy):**
   * **Che giau du lieu nhay cam (PII Masking):** So dien thoai va ten khach hang trong du lieu phan hoi cua Chatbot duoc che giau mot phan (vi du: `098***4321`) de tranh ro ri thong tin ca nhan.
   * **Phan quyen tra cuu don hang:** Chatbot chi cho phep tra cuu don hang khi khach hang cung cap chinh xac ca Ma don hang va So dien thoai trung khop voi co so du lieu.
   * **Phong chong Dau doc Ho so (Profile Poisoning):** Co che `ChatbotMemoryManager` phan tach ro rang giua Context thuc don va du lieu nguoi dung nhap, su dung cac khuon mau Prompt chat che ngan chan tan cong Prompt Injection.
