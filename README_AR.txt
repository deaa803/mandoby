تعديل أرباح المنصة اعتمادًا على الدفعات الفعلية

القاعدة الجديدة:
عمولة المنصة = مجموع دفعات الطلب × 2٪

التعديل يعمل تلقائيًا عند:
- إضافة دفعة من الـ API.
- إضافة دفعة من لوحة Filament.
- تعديل دفعة.
- حذف دفعة.
- نقل الدفعة من طلب إلى طلب آخر.

كما تم تعديل لوحة التحكم لتقرأ الأرباح مباشرة من جدول payments، وليس من قيمة ثابتة داخل الطلب.

طريقة التركيب:
1) خذ نسخة احتياطية:
   Copy-Item app app_backup_before_payment_profit -Recurse -Force
   Copy-Item resources resources_backup_before_payment_profit -Recurse -Force

2) فك الضغط داخل جذر مشروع Laravel بجانب artisan:
   Expand-Archive -Path ".\Mandoby_Payment_Profit_Fixed.zip" -DestinationPath "." -Force

3) نظف الكاش:
   php artisan optimize:clear
   composer dump-autoload

4) حدث الطلبات القديمة من الدفعات الموجودة:
   php artisan payments:sync-profits

5) أعد تشغيل السيرفر:
   php artisan serve --host=0.0.0.0 --port=8000

مهم:
- نسبة العمولة الحالية 2٪.
- لتغييرها لاحقًا عدّل COMMISSION_RATE داخل:
  app/Services/PlatformProfitService.php
