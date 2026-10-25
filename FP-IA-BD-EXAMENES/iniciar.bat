@echo off
title FP IA BD - Exam Center
cd /d "%~dp0"
where php >nul 2>nul
if %errorlevel%==0 goto START

if exist "C:\xampp\php\php.exe" (
  set "PHPBIN=C:\xampp\php\php.exe"
  goto START_XAMPP
)

echo.
echo ============================================================
echo PHP no se encuentra en PATH.
echo.
echo Instala PHP 8.x o XAMPP y vuelve a ejecutar este archivo.
echo Consulta docs\COMO-EJECUTAR.md
echo ============================================================
echo.
pause
exit /b 1

:START
echo Iniciando FP IA ^& Big Data Exam Center...
start "" "http://localhost:8080"
php -S localhost:8080
goto END

:START_XAMPP
echo Usando PHP de XAMPP...
start "" "http://localhost:8080"
"%PHPBIN%" -S localhost:8080

:END
pause
