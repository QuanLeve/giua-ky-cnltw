@echo off
if not exist "C:\xampp\php\php.exe" (
    echo Khong tim thay PHP XAMPP tai C:\xampp\php\php.exe.
    exit /b 1
)
if not exist "%~dp0var\tmp" mkdir "%~dp0var\tmp"
echo Ung dung: http://127.0.0.1:8000/  (Ctrl+C de dung)
"C:\xampp\php\php.exe" -d "upload_tmp_dir=%~dp0var\tmp" -d "sys_temp_dir=%~dp0var\tmp" -S 127.0.0.1:8000 -t "%~dp0." "%~dp0router.php"
