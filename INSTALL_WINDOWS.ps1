$ErrorActionPreference = "Stop"

Write-Host "إنشاء نسخة احتياطية..."
Copy-Item app\Filament app\Filament_backup -Recurse -Force
Copy-Item app\Providers\Filament\AdminPanelProvider.php app\Providers\Filament\AdminPanelProvider.backup.php -Force

Write-Host "فك الحزمة واستبدال الملفات..."
Expand-Archive -Path .\Filament_Complete_Ready.zip -DestinationPath . -Force

Write-Host "مسح الكاش وتحديث autoload..."
php artisan optimize:clear
composer dump-autoload

Write-Host "تم. شغّل: php artisan serve"
