@echo off
setlocal EnableDelayedExpansion
title Food Rescue - Unified Launcher
color 0A

:: ============================================================
::   FOOD RESCUE - UNIFIED APPLICATION LAUNCHER
::   Controls: Oracle DB + Apache + Frontend Server
:: ============================================================

set "ORACLE_HOME=C:\app\Asus\product\21c\dbhomeXE"
set "ORACLE_BIN=%ORACLE_HOME%\bin"
set "XAMPP_HOME=C:\xampp"
set "APACHE_BIN=%XAMPP_HOME%\apache\bin\httpd.exe"
set "APACHE_CONF=%XAMPP_HOME%\apache\conf\httpd.conf"
set "FRONTEND_DIR=%~dp0food-rescue-web"
set "PORT=3000"

cls
echo.
echo  ============================================================
echo   ^>^>  FOOD RESCUE  ^<^<   Unified Management System
echo  ============================================================
echo.

:: ─────────────────────────────────────────────────────────────
:: STEP 1: Admin Check
:: ─────────────────────────────────────────────────────────────
net session >nul 2>&1
if %errorLevel% NEQ 0 (
    echo  [!] Requesting Administrator privileges...
    powershell -Command "Start-Process '%~f0' -Verb RunAs"
    exit /b
)

:: ─────────────────────────────────────────────────────────────
:: STEP 2: Pre-Launch Cleanup (Ensures a Fresh Start)
:: ─────────────────────────────────────────────────────────────
echo  [1/6] Cleaning up existing sessions...
taskkill /f /im httpd.exe >nul 2>&1
for /f "tokens=5" %%a in ('netstat -ano ^| findstr ":%PORT% " ^| findstr "LISTENING" 2^>nul') do (
    taskkill /f /pid %%a >nul 2>&1
)
echo        Cleanup complete.
echo.

:: ─────────────────────────────────────────────────────────────
:: STEP 3: Start Oracle Services
:: ─────────────────────────────────────────────────────────────
echo  [2/6] Verifying Oracle Database Services...
sc query OracleServiceXE | findstr "RUNNING" >nul 2>&1
if %errorLevel% NEQ 0 (
    echo        Starting OracleServiceXE...
    net start OracleServiceXE >nul 2>&1
    timeout /t 5 /nobreak >nul
)
sc query OracleOraDB21Home1TNSListener | findstr "RUNNING" >nul 2>&1
if %errorLevel% NEQ 0 (
    echo        Starting TNS Listener...
    net start OracleOraDB21Home1TNSListener >nul 2>&1
)
echo        Oracle services are ready.
echo.

:: ─────────────────────────────────────────────────────────────
:: STEP 4: Initialise Database
:: ─────────────────────────────────────────────────────────────
echo  [3/6] Opening XEPDB1 Database...
echo ALTER PLUGGABLE DATABASE XEPDB1 OPEN; EXIT; | "%ORACLE_BIN%\sqlplus.exe" -S "/ as sysdba" >nul 2>&1
echo        Database is open.
echo.

:: ─────────────────────────────────────────────────────────────
:: STEP 5: Start Apache (Backend API)
:: ─────────────────────────────────────────────────────────────
echo  [4/6] Launching Apache Backend...
set "PATH=%ORACLE_BIN%;%PATH%"
start /b "" "%APACHE_BIN%" -f "%APACHE_CONF%"
timeout /t 2 /nobreak >nul
echo        API Server running on port 80.
echo.

:: ─────────────────────────────────────────────────────────────
:: STEP 6: Start Frontend (Web UI)
:: ─────────────────────────────────────────────────────────────
echo  [5/6] Launching Frontend Server...
cd /d "%FRONTEND_DIR%"
start "Food Rescue Frontend" /min cmd /c "npx -y serve . -l %PORT% --no-clipboard"
timeout /t 3 /nobreak >nul
echo        Web UI running on port %PORT%.
echo.

:: ─────────────────────────────────────────────────────────────
:: FINISH: Display Access Links
:: ─────────────────────────────────────────────────────────────
echo  [6/6] Application is ready!
echo.
echo  ============================================================
echo   ACCESS INFORMATION
echo  ============================================================
echo.
echo   >> WEB APPLICATION : http://localhost:%PORT%
echo   >> BACKEND DIAGNOSTICS : http://localhost/food-rescue-api/test_connection.php
echo.
echo  ============================================================
echo.
echo  Opening browser...
start "" "http://localhost:%PORT%"

echo  KEEP THIS WINDOW OPEN while using the application.
echo  Press CTRL+C or Close this window to exit.
echo.

:: Keep script alive
pause >nul
