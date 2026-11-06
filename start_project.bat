@echo off
title Khoi dong CIVILIZE Restaurant
color 0A

echo ========================================================
echo    DANG KHOI DONG CIVILIZE RESTAURANT (LARAVEL)
echo ========================================================
echo.

:: 1. Khoi dong MySQL (XAMPP) neu chua chay
netstat -ano | findstr :3306 >nul
if %errorlevel% equ 0 (
    echo [OK] MySQL da dang chay tren cong 3306.
) else (
    echo [*] Dang khoi dong MySQL tu XAMPP...
    start "" /B "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone
    timeout /t 3 >nul
    echo [OK] MySQL da khoi dong thanh cong!
)

echo.
:: 2. Mo trinh duyet
echo [*] Dang mo trinh duyet http://127.0.0.1:8000 ...
start http://127.0.0.1:8000

echo.
:: 3. Chay Laravel Server
echo [*] Dang chay Laravel Web Server...
echo [!] Vui long khong tat cua so nay khi dang su dung website!
echo ========================================================
echo.

php artisan serve
pause
