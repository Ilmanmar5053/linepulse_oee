@echo off
title OEE Performance System - LAN Web Server
color 0b
echo ========================================================
echo        OEE PERFORMANCE SYSTEM - LOCAL LAN SERVER
echo ========================================================
echo.
echo IP Komputer Host ini:
ipconfig | findstr /i "IPv4"
echo.
echo Server siap diakses oleh komputer lain dalam 1 jaringan lokal.
echo Contoh URL akses dari komputer lain:
echo    http://192.168.10.99:8000  (atau sesuai IP jaringan LAN anda)
echo.
echo Tekan Ctrl + C untuk mematikan server.
echo ========================================================
echo.

php artisan serve --host=0.0.0.0 --port=8000
pause
