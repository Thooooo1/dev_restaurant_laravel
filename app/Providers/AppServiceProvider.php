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

        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        } elseif (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1)) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Tự động chuẩn hóa dữ liệu thực đơn và sản phẩm
        try {
            \Illuminate\Support\Facades\DB::table('product')
                ->where('name', 'like', '%Haniken%')
                ->update(['name' => 'Bia Heineken', 'slug' => 'bia-heineken']);

            $mainDishes = [
                52 => 369000,
                53 => 359000,
                54 => 319000,
                55 => 299000,
                60 => 129000,
                62 => 85000,
                83 => 79000,
                84 => 149000,
                85 => 199000,
                86 => 169000,
            ];
            foreach ($mainDishes as $pid => $price) {
                \Illuminate\Support\Facades\DB::table('product')
                    ->where('id', $pid)
                    ->where('price_buy', '<', 100000)
                    ->update(['price_buy' => $price, 'price_sale' => $price]);
            }
        } catch (\Throwable $e) {
            // Khong anh huong den ung dung neu DB dang khoi tao
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
