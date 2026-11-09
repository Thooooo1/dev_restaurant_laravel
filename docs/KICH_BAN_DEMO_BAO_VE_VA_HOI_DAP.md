# KICH BAN DEMO THUYET TRINH VA BO CAU HOI PHAN BIEN HOI DONG
## DO AN TOT NGHIEP: HE THONG QUAN LY NHA HANG, POS VA GOI MON QR CIVILIZE

---

### PHAN 1: KICH BAN THUYET TRINH & DEMO TRUC TIEP (5 - 7 PHUT)

#### Phut 0 - 1: Mo Dau Va Dat Van De
* **Noi dung trinh bay:**
  "Kinh thua quy thay co trong Hoi dong cham thi tot nghiep. Trong boi canh nganh F&B chuyen doi so manh me, viec toi uu hoa chi phi nhan su, han che sai sot goi mon va nang cao trai nghiem thuc khach la yeu cau song con. Nhom em da nghien cuu va xay dung he thong **CIVILIZE Restaurant** - He thong quan ly nha hang, diem ban hang POS va goi mon thong minh qua ma QR tren nen tang Laravel 11 va PHP 8.2."
* **Hanh dong tren man hinh:**
  Mo trang chu `civilizerestaurant.id.vn` tren may chieu, dong thoi mo giao dien Mobile tren dien thoai.

#### Phut 1 - 3: Trai Nghiem Khach Hang (Dine-in QR & Online Order)
* **Noi dung trinh bay:**
  "Diem dac sac dau tien la trai nghiem khong cham tai ban: Thuc khach chi can dung dien thoai quet ma QR co dinh tren ban an ma khong can cai dat bat ky ung dung nao."
* **Hanh dong tren man hinh:**
  1. Quet ma QR Ban 15 -> Giao dien chuyen sang che do goi mon tai ban, hien thi chi tiet so ban va tang.
  2. Chon 2 mon an -> Gio hang tu dong ap dung chiet khau ban tiec qua `PricingService`.
  3. Bam "Gui yeu cau goi mon".
  4. Chuyen tab sang Man hinh Bep (Kitchen Order Ticket): Mon moi hien thi tuc thi, nhan vien bep bam "Dang che bien" -> "Da len ban".
  5. Tren dien thoai, bam nut "Goi nhan vien phuc vu" (vi du: Xin them da / goi thanh toan) -> He thong hien thi chuong thong bao truc quan cho nhan vien thu ngan.

#### Phut 3 - 4: Thanh Toan Tu Dong VietQR & Theo Doi Don Giao Hang
* **Noi dung trinh bay:**
  "He thong ho tro cong thanh toan so hien dai: Chuyen khoan VietQR doi soat tu dong qua Webhook va theo doi giao hang truc quan."
* **Hanh dong tren man hinh:**
  1. Mo trang hoa don ban hoac don online -> Ma VietQR dong sinh ra chua dung cu phap ma don `ORD...`.
  2. Nhan vien bam nut "Mo phong Webhook thanh toan" (che do kiem thu `PAYMENT_MODE=demo`) -> He thong lap tuc xac nhan don hang thanh cong, ban an tu dong tra ve trang thai trong.
  3. Tai man hinh don giao hang: Nhan vien nhap ten shipper ngoai, so dien thoai va bam cap nhat tien do. Khach hang mo link don hang thay ngay Timeline 4 buoc kem nut goi dien / Zalo shipper truc tiep.

#### Phut 4 - 5: Tro Ly Ao AI Thuc Don & Nghiep Vu Quan Tri POS
* **Noi dung trinh bay:**
  "He thong tich hop Tro ly ao Google Gemini AI giup tu van mon an dua tren so thich khach hang va tra cuu thong tin don hang an toan."
* **Hanh dong tren man hinh:**
  1. Mo khung chat Chatbot -> Hoi: "Toi muon an mon hai san cay nhe cho 2 nguoi". Chatbot phan tich menu he thong va goi y mon kem gia chinh xac.
  2. Hoi tra cuu don hang: Chatbot yeu cau cung cap dung ca Ma don va So dien thoai, dong thoi che giau thong tin nhay cam (PII Masking).
  3. Mo man hinh POS: Thuc hien thao tac tach ban, chuyen ban va huy 1 mon an da vao bep kem ly do -> He thong ghi vet vao nhat ky kiem toan `OrderItemLog` (Huy mem, bao toan du lieu).

#### Phut 5 - 7: Tong Ket Kien Truc, Bao Mat Va Kiem Thu Tu Dong
* **Noi dung trinh bay:**
  "Ve mat ky thuat, du an dat tieu chuan san pham san sang van hanh:
  - Dong goi tai nguyen cuc bo 100% qua Vite (Tailwind CSS, Font Awesome), hoat dong offline muot ma khong phu thuoc CDN ngoai.
  - Kien truc an toan du lieu voi Pessimistic Locking (`lockForUpdate`) va DB Transaction chong tranh chap.
  - Bo kiem thu tu dong 35 test case dat 100% passed tich hop CI/CD GitHub Actions.
  Em xin chan thanh cam on quy thay co va san sang tiep nhan cau hoi phan bien."

---

### PHAN 2: BO 10 CAU HOI PHAN BIEN HOC BUA VA CAU TRA LOI MAU

#### Cau Hoi 1: Tai sao du an su dung Polling 5 giay ma khong dung WebSocket hoac Pusher?
* **Tra loi chuan:**
  "Kinh thua thay co, viec lua chon ky thuat duoc dua tren yeu cau thuc te va do phuc tap he thong:
  1. **Do tin cay va chi phi ha tang:** WebSocket yeu cau may chu duy tri Socket Daemon (nhu Laravel Reverb / Node.js) hoac ton chi phi subscription ben thu ba (nhu Pusher). Tren cac moi truong hosting tieu chuan hoac may chu noi bo cua nha hang, viec duy tri persistent socket de bi ngat ket noi khi mang WiFi chap chon.
  2. **Toi uu hoa tai nguyen (Conditional Polling):** Endpoint polling cua he thong `/admin/notifications/check-new` chi thuc hien truy van tren chi muc thoi gian `created_at > timestamp` va tra ve payload JSON cuc ky nho (< 1 KB), thoi gian phan hoi chi khoang 15-30ms, tieu thu CPU va RAM khong dang ke.
  3. Tuy nhien, he thong duoc kien truc huong su kien (Event-Driven) san sang, khi quy mo nha hang mo rong, chung em co the chuyen doi sang Laravel Reverb chi bang cach phat Broadcast Event ma khong can thay doi logic nghiep vu ben duoi."

#### Cau Hoi 2: Neu co 2 nhan vien hoac 2 khach hang cung bam thanh toan hoac dat mon cuoi cung trong kho tai mot thoi diem thi he thong xu ly ra sao?
* **Tra loi chuan:**
  "Kinh thua thay co, he thong da giai quyet triet de van de Tranh chap dong thoi (Race Condition) bang hai co che:
  1. **Co so du lieu su dung Khoa bi quan (Pessimistic Locking):** Trong controller `checkout` va `PaymentWebhookController`, toan bo logic duoc boc trong `DB::transaction()` ket hop `lockForUpdate()`. Vi du khi doc dong san pham hoac ban an, lenh `SELECT ... FOR UPDATE` se khoa dong do trong InnoDb. Giao dich thu hai bat buoc phai cho den khi giao dich thu nhat commit hoac rollback.
  2. **Kiem tra lai ton kho va trang thai thuc te:** Sau khi lay duoc quyen lock, he thong kiem tra lai `qty >= requested_qty`. Neu khong du, giao dich tu dong rollback va nem ve loi than thien cho nguoi dung. Nho do so luong ton kho khong bao gio bi am va ban an khong bao gio bi thanh toan hai lan."

#### Cau Hoi 3: Lam the nao de ngan chan ke xau gui request gia mao vao Webhook thanh toan VietQR de xac nhan don hang ma khong chuyen tien?
* **Tra loi chuan:**
  "Kinh thua thay co, lop phong thu Webhook duoc thiet ke da tang:
  1. **Xac thuc Token bao mat trong HTTP Header:** Endpoint Webhook bat buoc kiem tra header `Authorization: Apikey <token>` hoac `secure-token`. Token nay duoc luu bao mat trong file cau hinh `.env` va chi nha cung cap cong thanh toan (Casso / SePay / Ngan hang) duoc biet.
  2. **Doi chieu cu phap va so tien tuyet doi:** Webhook khong chi don thuan nhan tin hieu, ma phai boc tach ma don qua Regex (vi du: `ORD(\d+)`), sau do so sanh `amount >= order.total_money`. Neu so tien thap hon du chi 1 dong, he thong se tu choi xac nhan.
  3. **Tach biet moi truong Demo va Production:** Trong che do `PAYMENT_MODE=production`, toan bo endpoint gia lap test se lap tuc bi chan boi Middleware `EnsurePaymentDemoMode` va tra ve loi 404 Not Found."

#### Cau Hoi 4: Tai sao du an khong thiet ke mot Role Shipper rieng kem theo tai khoan dang nhap?
* **Tra loi chuan:**
  "Kinh thua thay co, day la mot quyet dinh thiet ke dua tren khao sat mo hinh van hanh thuc te cua cac nha hang quy mo vua va nho:
  1. **Thuc te van hanh:** Da so nha hang khong so huu doi ngu giao hang co dinh ma thue dich vu ngoai theo chuyen (GrabExpress, Ahamove, BeDelivery) hoac goi shipper quen. Viec bat buoc cac tai xe ngoai phai tao tai khoan, nho mat khau va dang nhap vao he thong la hoan toan bat kha thi trong quy trinh thuc te.
  2. **Toi uu bao mat va phan quyen:** Viec them mot Role moi dong nghia voi viec mo rong be mat tan cong (Attack Surface), them rui lo hong Session va mat khau yeu tu tai xe.
  3. **Giai phap tinh gon:** Chung em luu truc tiep `shipper_name`, `shipper_phone` va `delivery_status` vao don hang. Nhan vien nha hang la nguoi truc tiep quan ly va cap nhat trang thai sau khi shipper nhan hang. Khach hang van co day du trai nghiem theo doi hanh trinh va nut bam goi / Zalo truc tiep cho tai xe."

#### Cau Hoi 5: Tro ly ao AI su dung co che gi? Lam sao ngan chan du lieu sai lech ve mon an va chong ro ri thong tin don hang?
* **Tra loi chuan:**
  "Kinh thua thay co, he thong tich hop Google Gemini API voi kien truc RAG tinh gon (Retrieval-Augmented Generation):
  1. **Kien thuc thuc don duoc chuan hoa qua `ChatbotKnowledgeBase`:** Thay vi de AI tu do suy doan, he thong truy van co so du lieu thuc te ve mon an, gia ca, danh muc, thoi gian mo cua roi nap vao System Prompt lam ngu canh (Grounding Context). Chatbot co quy tac chi tra loi nhung gi nam trong thuc don nha hang.
  2. **Bao ve quyen rieng tu (PII Masking):** Khi chatbot tra loi ve thong tin nguoi dung hoac don hang, he thong tu dong che giau so dien thoai (vi du: `098***4321`) va ten khach hang.
  3. **Yeu cau xac thuc 2 yeu to khi tra don:** Khach hang muon hoi ve don phai cung cap dung ca Ma don va So dien thoai dat hang. Neu chi biet ma don cua nguoi khac, chatbot se tu choi cung cap thong tin de bao ve tinh rieng tu."

#### Cau Hoi 6: Lam sao ung dung dam bao chay on dinh khi phong thi hoac nha hang bi su co ket noi mang Internet?
* **Tra loi chuan:**
  "Kinh thua thay co, chung em da nang cap he thong len kien truc Offline-First ve mat giao dien:
  1. **Bundling tai nguyen cuc bo 100% bang Vite:** Truoc day ung dung phu thuoc vao CDN ngoai (Tailwind Play CDN, Font Awesome CDN). Neu mang cham hoac mat ket noi, giao dien se bi vo va mat toan bo bieu tuong icon.
  2. **Build cuc bo san sang:** Hien tai toan bo ma CSS, JavaScript va bo font (Woff2, TTF) da duoc build thanh cac bundle toi uu hoa nam trong `public/build`.
  3. **Co che Fallback thong minh:** Trong Layout Blade, chung em thiet lap kiem tra `file_exists(public_path('build/manifest.json'))`. Neu co file build cuc bo, ung dung se nap asset tu may chu; neu khong co, ung dung tu dong chuyen sang CDN du phong."

#### Cau Hoi 7: Khi nhan vien huy mot mon an tren ban da gui vao bep, tai sao he thong khong xoa dong do trong database ma lai dung Soft Cancellation?
* **Tra loi chuan:**
  "Kinh thua thay co, trong quan ly nha hang nghiep vu kiem toan va chong gian lan la cuc ky quan trong:
  1. **Chong that thoat va gian lan noi bo:** Neu cho phep xoa vat ly (Hard Delete), nhan vien phuc vu co the thong dong voi khach: goi mon len ban cho khach dung, sau do xoa mon khoi he thong de bo tien tui rieng.
  2. **Bao toan nhat ky bep:** Mon an da bao vao bep nghia la nguyen lieu da duoc xu ly. Khi huy mon, he thong danh dau `is_cancelled = 1` va ghi vao bang `order_item_logs` kem ID nhan vien thao tac va ly do huy. Nho do quan ly co the doi chieu luong nguyen lieu hao hut cua bep voi so mon bi huy tren he thong."

#### Cau Hoi 8: Logic tinh toan phi giao hang va tong tien duoc xu ly the nao de dam bao tinh nhat quan va chong gian lan client-side?
* **Tra loi chuan:**
  "Kinh thua thay co:
  1. **Tap trung logic nghiep vu vao `PricingService`:** Truoc day, logic tinh tien nam rai rac o ca Frontend JavaScript, CartController va View. Chung em da tai cau truc va dua toan bo ve mot Service duy nhat `app/Services/PricingService.php`.
  2. **Tuyet doi khong tin tuong du lieu tu Client:** Khi checkout, he thong khong lay gia hay tong tien ma trinh duyet gui len. He thong chi nhan Danh sach san pham va So luong, sau do truy van lai don gia moi nhat tu Database va tinh toan phi ship dua tren cau hinh `settings.json`. Nho do nguoi dung khong the can thiep vao gia tri don hang bang cach sua ma HTML hay gui request gia mao."

#### Cau Hoi 9: Co che xac thuc va phan quyen trong ung dung duoc thiet ke nhu the nao?
* **Tra loi chuan:**
  "Kinh thua thay co:
  1. **Phan tang vai tro bang Middleware:** He thong phan quyen ro rang qua Middleware `auth` ket hop kiem tra cot `roles`: Role 1 (Admin), Role 2 (Employee / Nhan vien) va Role 3 (Customer / Khach hang).
  2. **Chuyen doi toan bo thao tac quan tri sang POST CSRF:** Chung em da loai bo hoan toan cac route quan tri dung phuong thuc GET de thay doi trang thai (nhu xoa san pham, doi trang thai ban, dang xuat). Tat ca deu bat buoc su dung phuong thuc `POST` co `@csrf`, loai bo 100% nguy co bi tan cong Cross-Site Request Forgery."

#### Cau Hoi 10: Ban da thuc hien nhung bai kiem thu tu dong nao va quy trinh CI/CD ra sao?
* **Tra loi chuan:**
  "Kinh thua thay co:
  1. **Bo kiem thu tu dong (Automated Feature Tests):** Chung em da xay dung bo test bao phu toan bo cac tinh huong ranh gioi (Edge cases):
     - `SecurityAndPaymentTest`: Kiem tra phan quyen, chong gia mao webhook, kiem tra chan GET 405 tren 33 route admin.
     - `ChatbotSecurityTest`: Kiem tra chong ro ri PII, chong injection, kiem tra xac thuc don hang.
     - `DeliveryAndPricingTest`: Kiem tra tinh phi ship, chiet khau, Enum phuong thuc thanh toan va nhan trang thai giao hang.
  2. **Tich hop lien tuc CI/CD qua GitHub Actions:** Chung em da cau hinh workflow `.github/workflows/tests.yml`. Moi khi co commit moi duoc day len GitHub, he thong se tu dong khoi chay moi truong container MySQL 8.0, chay toan bo migration va bo test. Neu co bat ky loi nghiep vu nao, build se bi chan lai ngay lap tuc."
