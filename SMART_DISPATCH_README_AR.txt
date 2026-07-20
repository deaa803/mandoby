نظام Mandoby Smart Dispatch
============================

الفكرة
------
تحسب المنصة وزن الطلب اعتمادًا على:

وزن الطلب = مجموع (عدد الطرود × وزن الطرد الواحد)

ثم تضيف هامش أمان 10%، وتبحث فقط ضمن سائقي الشركة صاحبة الطلب.
كل سائق مرتبط بسيارته، لذلك يقارن النظام الحمولة المطلوبة مع حمولة سيارة السائق.

يوجد خياران للشركة:
1) اقتراح سائق ذكي: يعرض السائقين المناسبين مرتبين حسب ملاءمة الحمولة والقرب وضغط العمل.
2) اختيار سائق يدويًا: تختار الشركة سائقًا من نفس الشركة بشرط أن تكون حمولة سيارته كافية.

الحقول الجديدة
--------------
product_details.package_weight_kg
    وزن الطرد الواحد بالكيلوغرام.

company_cars.max_load_kg
    الحمولة القصوى الآمنة للسيارة بالكيلوغرام.

orders.total_weight_kg
    الوزن الفعلي الكامل للطلب.

orders.required_load_kg
    الحمولة المطلوبة بعد إضافة هامش الأمان.

orders.driver_assignment_method
    smart أو manual.

orders.driver_assigned_at
    وقت تعيين السائق.

order_product_detail.package_weight_kg
order_product_detail.line_weight_kg
    نسخة محفوظة من وزن الطرد ووزن سطر الطلب حتى لا يتغير الطلب القديم عند تعديل المنتج لاحقًا.

التركيب
-------
1) فك الحزمة داخل جذر مشروع Laravel بجانب artisan.
2) نفذ:

php artisan migrate
php artisan optimize:clear
composer dump-autoload

3) أدخل وزن الطرد لكل المنتجات القديمة، وأدخل الحمولة القصوى لكل سيارات الشركة.

4) أعد حساب أوزان الطلبات القديمة:

php artisan dispatch:sync-weights

لطلب واحد فقط:

php artisan dispatch:sync-weights --order=15

بيانات العرض التجريبي
---------------------
تم تعديل ShopifyCompanyProductsSeeder ليضع أوزانًا تجريبية للطرود.
لتحديث منتجات السيدر الموجودة:

php artisan db:seed --class=ShopifyCompanyProductsSeeder
php artisan dispatch:sync-weights

API تطبيق الشركة
----------------
إضافة منتج:
POST /api/product-details

أصبح الحقل التالي مطلوبًا:
package_weight_kg

إضافة سيارة وسائق:
POST /api/company/cars

أصبح الحقل التالي مطلوبًا:
max_load_kg

عرض الاقتراحات:
GET /api/company/orders/{order}/driver-recommendations

تعيين اقتراح ذكي:
POST /api/company/orders/{order}/assign-recommended-driver

يمكن إرسال driver_id لاختيار أحد الاقتراحات، أو عدم إرساله لاعتماد أول اقتراح.

الاختيار اليدوي:
POST /api/company/orders/{order}/assign-driver

Body:
{
  "driver_id": 3
}

لوحة Filament
-------------
تمت إضافة:
- وزن الطرد في المنتجات.
- حمولة السيارة في سيارات الشركات.
- وزن الطلب والحمولة المطلوبة في الطلبات.
- زر "اقتراح سائق ذكي".
- زر "اختيار سائق يدويًا".
- عرض السيارة المرتبطة وطريقة التعيين.

مهم
---
الحزمة المرفوعة تحتوي Backend + Filament فقط، ولا تحتوي مشروع Flutter.
الـ API جاهز لربط زري الاقتراح الذكي والاختيار اليدوي في تطبيق الشركة.

لتغيير هامش الأمان:
app/Services/SmartDispatchService.php

public const SAFETY_MARGIN_PERCENT = 10;
