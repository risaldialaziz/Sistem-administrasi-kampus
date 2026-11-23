@echo off
cd /d "%~dp0example-app"
where php >nul 2>nul
if errorlevel 1 (
    echo PHP tidak ditemukan pada PATH.
    pause
    exit /b 1
)
php artisan serve --host=127.0.0.1 --port=8000
