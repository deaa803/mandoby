نظام دفعات الشركات للمنصة

ما تمت إضافته:
- جدول platform_commission_payments.
- حساب المستحق والمدفوع والمتبقي لكل شركة.
- Resource جديد في Filament باسم: دفعات الشركات للمنصة.
- زر تسجيل دفعة من جدول الشركات.
- حالات: غير مسدد، مسدد جزئيًا، مسدد بالكامل.
- API لتطبيق الشركة:
  GET  /api/company/platform-account
  GET  /api/company/platform-payments
  POST /api/company/platform-payments
- الدفعة التي ترسلها الشركة تبقى pending حتى تؤكدها الإدارة.

بعد فك الضغط داخل جذر المشروع نفذ:
php artisan migrate
php artisan payments:sync-profits
php artisan optimize:clear
composer dump-autoload
php artisan storage:link

مهم:
لا يتم تصفير عمولة الطلب بعد الدفع. العمولة تبقى كسجل أرباح، ودفعات الشركة للمنصة تخصم من المبلغ المتبقي فقط.
