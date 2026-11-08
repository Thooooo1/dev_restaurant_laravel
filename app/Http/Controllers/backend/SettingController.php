<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    protected function getSettingsFilePath()
    {
        return storage_path('app/settings.json');
    }

    protected function defaultSettings()
    {
        return [
            // Thông tin nhà hàng
            'restaurant_name' => 'CIVILIZE - Nhà Hàng Phúc Thọ',
            'restaurant_slogan' => 'Tinh hoa ẩm thực truyền thống & Hiện đại',
            'hotline' => '0338990538',
            'email' => 'tnguyenanh189@gmail.com',
            'address' => 'Cụm 1, TT. Phúc Thọ, Huyện Phúc Thọ, TP. Hà Nội.',
            'opening_hours' => '09:00 – 23:00 (Tất cả các ngày trong tuần)',
            'map_embed_url' => 'https://www.google.com/maps/embed?...',

            // Cấu hình thanh toán VietQR
            'bank_id' => 'MB',
            'bank_name' => 'MBBank (Ngân hàng Quân Đội)',
            'bank_account_no' => '0988123456888',
            'bank_account_name' => 'NHA HANG CIVILIZE PHUC THO',
            'enable_vietqr' => true,
            'enable_momo' => true,
            'enable_cash' => true,

            // Cấu hình bán hàng & Đặt bàn
            'table_booking_discount_percent' => 10,
            'shipping_base_fee' => 25000,
            'shipping_free_threshold' => 500000,
            'max_delivery_distance_km' => 15,

            // Cấu hình chuông thông báo & âm thanh
            'sound_service_call' => true,
            'sound_new_order' => true,
            'polling_interval_seconds' => 5,
        ];
    }

    public function getSettings()
    {
        $path = $this->getSettingsFilePath();
        $defaults = $this->defaultSettings();

        if (File::exists($path)) {
            $saved = json_decode(File::get($path), true);
            if (is_array($saved)) {
                return array_merge($defaults, $saved);
            }
        }

        return $defaults;
    }

    public function index()
    {
        $settings = $this->getSettings();
        return view('backend.setting.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $current = $this->getSettings();

        $data = [
            'restaurant_name' => $request->input('restaurant_name', $current['restaurant_name']),
            'restaurant_slogan' => $request->input('restaurant_slogan', $current['restaurant_slogan']),
            'hotline' => $request->input('hotline', $current['hotline']),
            'email' => $request->input('email', $current['email']),
            'address' => $request->input('address', $current['address']),
            'opening_hours' => $request->input('opening_hours', $current['opening_hours']),

            'bank_id' => $request->input('bank_id', $current['bank_id']),
            'bank_name' => $request->input('bank_name', $current['bank_name']),
            'bank_account_no' => $request->input('bank_account_no', $current['bank_account_no']),
            'bank_account_name' => strtoupper($request->input('bank_account_name', $current['bank_account_name'])),
            'enable_vietqr' => $request->has('enable_vietqr'),
            'enable_momo' => $request->has('enable_momo'),
            'enable_cash' => $request->has('enable_cash'),

            'table_booking_discount_percent' => (int)$request->input('table_booking_discount_percent', $current['table_booking_discount_percent']),
            'shipping_base_fee' => (int)$request->input('shipping_base_fee', $current['shipping_base_fee']),
            'shipping_free_threshold' => (int)$request->input('shipping_free_threshold', $current['shipping_free_threshold']),
            'max_delivery_distance_km' => (int)$request->input('max_delivery_distance_km', $current['max_delivery_distance_km']),

            'sound_service_call' => $request->has('sound_service_call'),
            'sound_new_order' => $request->has('sound_new_order'),
            'polling_interval_seconds' => (int)$request->input('polling_interval_seconds', 5),
        ];

        File::put($this->getSettingsFilePath(), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return redirect()->route('admin.setting.index')->with('success', 'Đã lưu cấu hình cài đặt hệ thống thành công!');
    }
}
