@echo off
setlocal EnableDelayedExpansion
title Food Rescue - Unified Launcher
color 0A

:: ============================================================
::   FOOD RESCUE - UNIFIED APPLICATION LAUNCHER
::   Controls: Oracle DB + Apache + Automatic Workspace Sync
:: ============================================================

set "PROJECT_NAME=food-rescue-api"
set "ORACLE_HOME=C:\app\Asus\product\21c\dbhomeXE"
set "ORACLE_BIN=%ORACLE_HOME%\bin"
set "XAMPP_HOME=C:\xampp"
set "HTDOCS=%XAMPP_HOME%\htdocs"
set "APACHE_BIN=%XAMPP_HOME%\apache\bin\httpd.exe"
set "APACHE_CONF=%XAMPP_HOME%\apache\conf\httpd.conf"
set "APACHE_PORT=8080"
set "WORKSPACE=%~dp0backend"

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
:: STEP 2: Workspace Synchronization
:: ─────────────────────────────────────────────────────────────
echo  [1/6] Syncing Workspace to Apache...
if not exist "%WORKSPACE%" (
    echo        ERROR: Workspace folder 'backend' not found!
    echo        Ensure you are running this from the project root.
    pause
    exit /b
)

:: Sync API backend to htdocs / food-rescue-api
robocopy "%WORKSPACE%" "%HTDOCS%\food-rescue-api" /E /MT /R:0 /W:0 /NJH /NJS /NDL /NC /NS >nul
:: Sync Web frontend to htdocs (Root)
robocopy "%~dp0frontend" "%HTDOCS%" /E /MT /R:0 /W:0 /NJH /NJS /NDL /NC /NS >nul
echo        Synchronization complete.
echo.

:: ─────────────────────────────────────────────────────────────
:: STEP 3: Pre-Launch Cleanup
:: ─────────────────────────────────────────────────────────────
echo  [2/6] Cleaning up existing sessions...
taskkill /f /im httpd.exe >nul 2>&1
for /f "tokens=5" %%a in ('netstat -ano ^| findstr ":%APACHE_PORT% " ^| findstr "LISTENING" 2^>nul') do (
    taskkill /f /pid %%a >nul 2>&1
)
echo        Cleanup complete.
echo.

:: ─────────────────────────────────────────────────────────────
:: STEP 4: Start Oracle Services
:: ─────────────────────────────────────────────────────────────
echo  [3/6] Verifying Oracle Database Services...
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
:: STEP 5: Initialise Database
:: ─────────────────────────────────────────────────────────────
echo  [4/6] Opening XEPDB1 Database...
echo ALTER PLUGGABLE DATABASE XEPDB1 OPEN; EXIT; | "%ORACLE_BIN%\sqlplus.exe" -S "/ as sysdba" >nul 2>&1
echo        Database is open.
echo.

:: ─────────────────────────────────────────────────────────────
:: STEP 6: Start Apache (Unified Application)
:: ─────────────────────────────────────────────────────────────
echo  [5/6] Launching Unified Server...
set "PATH=%ORACLE_BIN%;%PATH%"
start /b "Food Rescue API Server" "%APACHE_BIN%" -f "%APACHE_CONF%"
timeout /t 2 /nobreak >nul
echo        Server running on port %APACHE_PORT%.
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
echo   >> APPLICATION URL : http://localhost:%APACHE_PORT%/index.php
echo.
echo  ============================================================
echo.
echo  Opening browser...
echo  Please notice: The application is now running on the root directory.
echo.
start "" "http://localhost:%APACHE_PORT%/index.php"

echo.
echo  ------------------------------------------------------------
echo  KEEP THIS WINDOW OPEN while using the application.
echo  This window monitors background services.
echo  Press any key to stop all services and exit.
echo  ------------------------------------------------------------
pause >nul

echo.
echo  Stopping services...
taskkill /f /im httpd.exe >nul 2>&1
echo  Exiting.
timeout /t 2 >nul
exit
