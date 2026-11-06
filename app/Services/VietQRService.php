<?php

namespace App\Services;

class VietQRService
{
    /**
     * Lấy thông tin tài khoản ngân hàng nhà hàng (ưu tiên đọc từ Cài đặt hệ thống settings.json)
     */
    public function getBankInfo(): array
    {
        $bankId = config('vietqr.bank_id', 'MB');
        $bankName = config('vietqr.bank_name', 'MBBank (Ngân Hàng Quân Đội)');
        $accountNo = config('vietqr.account_no', '0338990538');
        $accountName = config('vietqr.account_name', 'NHA HANG CIVILIZE');
        $template = config('vietqr.template', 'compact2');

        $settingsFile = storage_path('app/settings.json');
        if (file_exists($settingsFile)) {
            $settings = json_decode(file_get_contents($settingsFile), true);
            if (!empty($settings['bank_id'])) $bankId = $settings['bank_id'];
            if (!empty($settings['bank_name'])) $bankName = $settings['bank_name'];
            if (!empty($settings['bank_account_no'])) $accountNo = $settings['bank_account_no'];
            if (!empty($settings['bank_account_name'])) $accountName = $settings['bank_account_name'];
        }

        return [
            'bank_id' => $bankId,
            'bank_name' => $bankName,
            'account_no' => $accountNo,
            'account_name' => $accountName,
            'template' => $template,
        ];
    }

    /**
     * Sinh URL ảnh mã QR động theo chuẩn quốc gia VietQR (Napas 247)
     *
     * @param float|int $amount Số tiền thanh toán
     * @param string $memo Nội dung chuyển khoản (VD: BAN15 DH123)
     * @param string|null $template Mẫu giao diện QR (compact2, compact, qr_only, print)
     * @return string URL ảnh mã QR
     */
    public function generateQrUrl($amount, string $memo, ?string $template = null): string
    {
        $info = $this->getBankInfo();
        $bankId = $info['bank_id'];
        $accountNo = $info['account_no'];
        $accountName = $info['account_name'];
        $tpl = $template ?: $info['template'];

        $cleanAmount = (int) round($amount);
        $cleanMemo = urlencode(trim($memo));
        $cleanAccountName = urlencode(trim($accountName));

        return "https://img.vietqr.io/image/{$bankId}-{$accountNo}-{$tpl}.png?amount={$cleanAmount}&addInfo={$cleanMemo}&accountName={$cleanAccountName}";
    }

    /**
     * Trích xuất thông tin Đơn hàng (Order ID) hoặc Số bàn (Table ID) từ nội dung chuyển khoản ngân hàng
     *
     * @param string $content Nội dung chuyển khoản từ ngân hàng
     * @return array [ 'type' => 'order'|'table'|null, 'id' => int|null ]
     */
    public function parseTransferContent(string $content): array
    {
        $upperContent = mb_strtoupper($content, 'UTF-8');

        // 1. Tìm Order ID (DH123, ORDER123, DON123, DH 123, #123)
        if (preg_match('/(?:DH|ORDER|DON|HD|BILL|\#)\s*([0-9]+)/i', $upperContent, $matches)) {
            return [
                'type' => 'order',
                'id' => (int) $matches[1],
            ];
        }

        // 2. Tìm Table ID (BAN15, BAN 15, B15, BÀN 15)
        if (preg_match('/(?:BAN|BÀN|B)\s*([0-9]+)/iu', $upperContent, $matches)) {
            return [
                'type' => 'table',
                'id' => (int) $matches[1],
            ];
        }

        // 3. Fallback: Nếu chỉ có một cụm số duy nhất trong nội dung chuyển tiền
        if (preg_match('/\b([0-9]{1,6})\b/', $upperContent, $matches)) {
            return [
                'type' => 'order',
                'id' => (int) $matches[1],
            ];
        }

        return [
            'type' => null,
            'id' => null,
        ];
    }
}
