سيدر التصنيفات والميزات الكامل لمشروع Mandoby
================================================

المحتويات:
- database/seeders/DatabaseSeeder.php
- database/seeders/MarketplaceTaxonomySeeder.php
- database/seeders/ShopifyCompanyProductsSeeder.php (السيدر الموجود لديك بدون حذف)

ما تم إضافته:
- الاحتفاظ بجميع التصنيفات القديمة.
- إضافة التصنيفات الجديدة:
  رياضة ولياقة بدنية
  عناية شخصية وتجميل
  كتب ومجلات
  معدات وأدوات صناعية
  أطعمة ومشروبات
  هوايات وفنون
  خدمات
- إضافة مجموعة شاملة من ميزات المنتجات والخدمات.
- استخدام updateOrCreate و firstOrCreate لمنع التكرار عند تشغيل السيدر أكثر من مرة.
- عدم إيقاف db:seed إذا كان ملف Shopify CSV غير موجود؛ يتم تخطي استيراد المنتجات مع تحذير فقط.

التركيب:
1) فك الحزمة داخل جذر مشروع Laravel بجانب artisan.
2) نفّذ:

php artisan optimize:clear
composer dump-autoload
php artisan db:seed

لتشغيل التصنيفات والميزات وحدها:

php artisan db:seed --class=MarketplaceTaxonomySeeder

ملاحظة مهمة:
لا تستخدم migrate:fresh على قاعدة بيانات فيها معلومات حقيقية لأنه يحذف كل الجداول والبيانات.
