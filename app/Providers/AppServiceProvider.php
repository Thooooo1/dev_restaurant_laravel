<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Tu dong dong bo migration tren moi truong hosting khong co SSH khi co bang hoac cot moi
        $migrationFlag = storage_path('framework/migrated_20261114.flag');
        if (!File::exists($migrationFlag)) {
            try {
                if (!\Illuminate\Support\Facades\Schema::hasTable('order_delivery_logs') ||
                    !\Illuminate\Support\Facades\Schema::hasTable('payment_transactions') ||
                    !\Illuminate\Support\Facades\Schema::hasColumn('order', 'channel') ||
                    !\Illuminate\Support\Facades\Schema::hasColumn('order', 'payment_status')) {
                    @set_time_limit(180);
                    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                }
                @File::put($migrationFlag, (string)time());
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Auto-migration error: ' . $e->getMessage());
            }
        }

        // Xoa bo tai khoan chinhnv theo yeu cau quan tri
        $cleanupChinhFlag = storage_path('framework/cleanup_chinhnv.flag');
        if (!File::exists($cleanupChinhFlag)) {
            try {
                $chinh = \App\Models\User::withTrashed()
                    ->where('username', 'chinhnv')
                    ->orWhere('email', 'chinhnv24052001@gmail.com')
                    ->first();
                if ($chinh) {
                    \App\Models\Order::where('user_id', $chinh->id)->update(['user_id' => 1]);
                    $chinh->forceDelete();
                }
                @File::put($cleanupChinhFlag, (string)time());
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Cleanup chinhnv error: ' . $e->getMessage());
            }
        }

        // Xoa bo cac yeu cau tu van spam nuoc ngoai
        $cleanupSpamInquiriesFlag = storage_path('framework/cleanup_spam_inquiries.flag');
        if (!File::exists($cleanupSpamInquiriesFlag)) {
            try {
                \App\Models\Inquiries::where(function ($q) {
                    $q->where('name', 'like', '%Olivier%')
                      ->orWhere('name', 'like', '%Youssef%')
                      ->orWhere('name', 'like', '%DavidTrelf%')
                      ->orWhere('name', 'like', '%Balzac%')
                      ->orWhere('name', 'like', '%Abdullah%')
                      ->orWhere('phone', '87992624463')
                      ->orWhere('phone', '85472468266')
                      ->orWhere('phone', '81353553766');
                })->delete();
                @File::put($cleanupSpamInquiriesFlag, (string)time());
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Cleanup spam inquiries error: ' . $e->getMessage());
            }
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        } elseif (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1)) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        View::composer('*', function ($view) {
            static $settings = null;
            if ($settings === null) {
                $path = storage_path('app/settings.json');
                $defaults = [
                    'restaurant_name' => 'CIVILIZE - Nhà Hàng Phúc Thọ',
                    'restaurant_slogan' => 'Tinh hoa ẩm thực truyền thống & Hiện đại',
                    'hotline' => '0338990538',
                    'email' => 'tnguyenanh189@gmail.com',
                    'address' => 'Cụm 1, TT. Phúc Thọ, Huyện Phúc Thọ, TP. Hà Nội.',
                    'opening_hours' => '09:00 – 23:00 (Tất cả các ngày trong tuần)',
                ];

                if (File::exists($path)) {
                    $saved = json_decode(File::get($path), true);
                    if (is_array($saved)) {
                        $settings = array_merge($defaults, $saved);
                    } else {
                        $settings = $defaults;
                    }
                } else {
                    $settings = $defaults;
                }
            }

            $view->with('restaurantSettings', $settings);
        });
    }
}
