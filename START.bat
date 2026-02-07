@echo off
REM Resume AI - Quick Launcher
REM This batch file opens the app in your default browser

title Resume AI - Launcher
color 0A
cls

echo ========================================
echo.
echo   ^>^>^> RESUME AI - AUTOMATIC LAUNCHER
echo.
echo ========================================
echo.

REM Check if PHP server is running
tasklist /FI "imagename eq php.exe" >nul 2>&1
if %ERRORLEVEL% equ 0 (
    echo ✓ PHP Server is already running
    timeout /t 2 >nul
    goto open_browser
)

echo Starting PHP Development Server...
echo.
cd /d "%~dp0"
start "" php -S localhost:8000

echo ✓ Server starting on http://localhost:8000
echo Waiting for server to be ready...
timeout /t 3 >nul

:open_browser
echo.
echo Opening setup page in your browser...
echo.
start http://localhost:8000/setup.php

echo.
echo ========================================
echo Setup will open in your browser shortly
echo If it doesn't, visit: http://localhost:8000/setup.php
echo ========================================
echo.
timeout /t 2 >nul
