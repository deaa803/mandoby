حل نافذة: This page has expired

1) فك ضغط الحزمة داخل جذر مشروع Laravel ووافق على استبدال الملفين.

الملفات:
app/Filament/Widgets/SalesChart.php
app/Filament/Widgets/StatsOverview.php

2) عدّل ملف .env وتأكد من القيم التالية:

APP_URL=http://127.0.0.1:8000
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE=lax

3) نفّذ:

php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
composer dump-autoload

4) أوقف السيرفر بـ Ctrl + C ثم شغله:

php artisan serve

5) احذف Cookies الخاصة بالموقع 127.0.0.1:8000 أو افتح نافذة خاصة وسجّل الدخول من جديد.

مهم:
- استخدم دائمًا 127.0.0.1 ولا تنتقل بينه وبين localhost.
- لا تنفّذ php artisan key:generate كل مرة، لأن تغيير APP_KEY يبطل كل الجلسات.
