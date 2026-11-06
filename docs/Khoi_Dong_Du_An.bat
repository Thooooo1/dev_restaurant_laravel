@echo off
chcp 65001 >nul
title Khởi Động Dự Án Nhà Hàng CIVILIZE
color 0A

echo ========================================================
echo       ĐANG KHỞI ĐỘNG HỆ THỐNG NHÀ HÀNG CIVILIZE
echo ========================================================
echo.

:: 1. Kiểm tra và khởi động MySQL nếu chưa chạy
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo [OK] MySQL (Cơ sở dữ liệu) đã đang chạy.
) else (
    echo [*] Đang khởi động MySQL...
    start /B "" "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --console >nul 2>&1
    timeout /t 2 /nobreak >nul
    echo [OK] Đã bật MySQL thành công!
)

:: 2. Khởi động máy chủ Laravel
cd /d "D:\dev_restaurant_laravel\dev_restaurant_laravel"
echo [*] Đang khởi động Server Laravel trên cổng 8000...
start "Laravel Server - CIVILIZE" /min cmd /c "C:\xampp\php\php.exe artisan serve --host=0.0.0.0 --port=8000"

timeout /t 2 /nobreak >nul

:: 3. Tự động mở trình duyệt vào trang quản trị
echo [*] Đang mở trình duyệt vào Trang Quản Trị...
start http://localhost:8000/admin

echo.
echo ========================================================
echo       DỰ ÁN ĐÃ SẴN SÀNG!
echo       - Web Quản Trị: http://localhost:8000/admin
echo       - Web Khách Hàng: http://localhost:8000
echo ========================================================
echo.
timeout /t 5
exit
