$ErrorActionPreference = "Stop"

Write-Host "إنشاء نسخة احتياطية..." -ForegroundColor Yellow
$stamp = Get-Date -Format "yyyyMMdd_HHmmss"
$backup = "filament_backup_$stamp"
New-Item -ItemType Directory -Force -Path $backup | Out-Null

if (Test-Path "app\Filament") { Copy-Item "app\Filament" "$backup\Filament" -Recurse -Force }
if (Test-Path "app\Providers\Filament\AdminPanelProvider.php") {
    New-Item -ItemType Directory -Force -Path "$backup\Providers" | Out-Null
    Copy-Item "app\Providers\Filament\AdminPanelProvider.php" "$backup\Providers\AdminPanelProvider.php" -Force
}
if (Test-Path "resources\views\filament") {
    New-Item -ItemType Directory -Force -Path "$backup\views" | Out-Null
    Copy-Item "resources\views\filament" "$backup\views\filament" -Recurse -Force
}

Write-Host "نسخ الملفات المعدلة..." -ForegroundColor Cyan
Copy-Item ".\app\*" ".\app" -Recurse -Force
Copy-Item ".\resources\*" ".\resources" -Recurse -Force

Write-Host "تنظيف الكاش..." -ForegroundColor Cyan
php artisan optimize:clear
composer dump-autoload

Write-Host "تم التثبيت. النسخة الاحتياطية: $backup" -ForegroundColor Green
