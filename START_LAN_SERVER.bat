@echo off
title OEE Performance System - LAN Server
color 0B
cls
echo ================================================================
echo           OEE PERFORMANCE SYSTEM - LOCAL NETWORK SERVER
echo ================================================================
echo.
echo IP Jaringan Lokal (Pilih IP sesuai segmen LAN/WiFi Anda):
echo ----------------------------------------------------------------
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /c:"IPv4 Address" /c:"Alamat IPv4"') do (
    echo   -^> http:%%a:8000
)
echo ----------------------------------------------------------------
echo.
echo Menjalankan server pada 0.0.0.0:8000 ...
echo Tekan CTRL+C untuk menghentikan server.
echo.
php artisan serve --host=0.0.0.0 --port=8000
pause
