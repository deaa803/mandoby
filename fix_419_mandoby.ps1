$ErrorActionPreference = "Stop"

Write-Host ""
Write-Host "=== إصلاح خطأ 419 / Page Expired لمشروع Mandoby ===" -ForegroundColor Cyan
Write-Host ""

if (-not (Test-Path ".\artisan")) {
    Write-Host "خطأ: شغّل الملف من جذر مشروع Laravel، حيث يوجد artisan." -ForegroundColor Red
    exit 1
}

if (-not (Test-Path ".\.env")) {
    Write-Host "خطأ: ملف .env غير موجود." -ForegroundColor Red
    exit 1
}

$backupName = ".env.before-419-" + (Get-Date -Format "yyyyMMdd-HHmmss")
Copy-Item ".\.env" ".\$backupName" -Force
Write-Host "تم حفظ نسخة احتياطية: $backupName" -ForegroundColor Green

$envPath = (Resolve-Path ".\.env").Path
$lines = [System.Collections.Generic.List[string]]::new()

foreach ($line in [System.IO.File]::ReadAllLines($envPath)) {
    $lines.Add($line)
}

$settings = [ordered]@{
    "APP_URL"               = "http://127.0.0.1:8000"
    "SESSION_DRIVER"        = "file"
    "SESSION_LIFETIME"      = "120"
    "SESSION_ENCRYPT"       = "false"
    "SESSION_PATH"          = "/"
    "SESSION_DOMAIN"        = "null"
    "SESSION_SECURE_COOKIE" = "false"
    "SESSION_SAME_SITE"     = "lax"
    "SESSION_COOKIE"        = "mandoby_session_v2"
}

foreach ($key in $settings.Keys) {
    $pattern = "^" + [regex]::Escape($key) + "="
    $found = $false

    for ($i = 0; $i -lt $lines.Count; $i++) {
        if ($lines[$i] -match $pattern) {
            $lines[$i] = "$key=$($settings[$key])"
            $found = $true
        }
    }

    if (-not $found) {
        $lines.Add("$key=$($settings[$key])")
    }
}

$utf8NoBom = New-Object System.Text.UTF8Encoding($false)
[System.IO.File]::WriteAllLines($envPath, $lines, $utf8NoBom)

Write-Host "تم تحديث إعدادات الجلسة فقط، ولم يتم تغيير APP_KEY." -ForegroundColor Green

if (-not (Test-Path ".\storage\framework\sessions")) {
    New-Item -ItemType Directory -Path ".\storage\framework\sessions" -Force | Out-Null
}

Get-ChildItem ".\storage\framework\sessions" -File -ErrorAction SilentlyContinue |
    Remove-Item -Force -ErrorAction SilentlyContinue

Write-Host "تم حذف ملفات الجلسات المحلية القديمة." -ForegroundColor Green

php artisan optimize:clear

Write-Host ""
Write-Host "الإعدادات الفعلية بعد الإصلاح:" -ForegroundColor Cyan
php artisan tinker --execute="dump(config('app.url'), config('session.driver'), config('session.cookie'), config('session.domain'), config('session.secure'), config('session.same_site'));"

Write-Host ""
Write-Host "انتهى الإصلاح." -ForegroundColor Green
Write-Host "1) أوقف السيرفر بـ Ctrl + C."
Write-Host "2) شغله: php artisan serve --host=127.0.0.1 --port=8000"
Write-Host "3) احذف Cookies الخاصة بـ 127.0.0.1 أو افتح نافذة خاصة."
Write-Host "4) افتح: http://127.0.0.1:8000/admin"
Write-Host ""
