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

        View::composer('*', function ($view) {
            static $settings = null;
            if ($settings === null) {
                $path = storage_path('app/settings.json');
                $defaults = [
                    'restaurant_name' => 'CIVILIZE - Nhà Hàng Phúc Thọ',
                    'restaurant_slogan' => 'Tinh hoa ẩm thực truyền thống & Hiện đại',
                    'hotline' => '0338 990 538',
                    'email' => 'tnguyenanh189@gmail.com',
                    'address' => '120 Phúc Hòa, TT. Phúc Thọ, Huyện Phúc Thọ, TP. Hà Nội',
                    'opening_hours' => '09:00 – 23:00 tất cả các ngày trong tuần',
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
