$ErrorActionPreference = "Stop"

Write-Host ""
Write-Host "=== Fix Laravel 419 / Page Expired ===" -ForegroundColor Cyan
Write-Host ""

if (-not (Test-Path ".\artisan")) {
    Write-Host "ERROR: Run this script from the Laravel project root." -ForegroundColor Red
    exit 1
}

if (-not (Test-Path ".\.env")) {
    Write-Host "ERROR: .env file was not found." -ForegroundColor Red
    exit 1
}

$backupName = ".env.before-419-" + (Get-Date -Format "yyyyMMdd-HHmmss")
Copy-Item ".\.env" ".\$backupName" -Force
Write-Host "Backup created: $backupName" -ForegroundColor Green

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

Write-Host "Session settings updated. APP_KEY was not changed." -ForegroundColor Green

if (-not (Test-Path ".\storage\framework\sessions")) {
    New-Item -ItemType Directory -Path ".\storage\framework\sessions" -Force | Out-Null
}

Get-ChildItem ".\storage\framework\sessions" -File -ErrorAction SilentlyContinue |
    Remove-Item -Force -ErrorAction SilentlyContinue

Write-Host "Old file sessions removed." -ForegroundColor Green

php artisan optimize:clear
php artisan config:clear
php artisan cache:clear

Write-Host ""
Write-Host "Effective session settings:" -ForegroundColor Cyan
php artisan tinker --execute="dump(config('app.url'), config('session.driver'), config('session.cookie'), config('session.domain'), config('session.secure'), config('session.same_site'));"

Write-Host ""
Write-Host "Done." -ForegroundColor Green
Write-Host "1. Stop the server with Ctrl+C"
Write-Host "2. Start it with: php artisan serve --host=0.0.0.0 --port=8000"
Write-Host "3. Clear cookies for 127.0.0.1 or open an Incognito window"
Write-Host "4. Open: http://127.0.0.1:8000/admin"
Write-Host ""
