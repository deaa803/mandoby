# Delivery / QR / Seeder / Migration Cleanup

## Migrations cleanup

تم دمج migrations الإضافية داخل migrations الإنشاء الأساسية:

- حقول ETA و QR والتسليم أصبحت داخل `2026_06_17_201243_create_orders_table.php`.
- حقول `delivery_radius_km` و `extra_delivery_fee_per_km` أصبحت داخل `2026_06_17_201233_create_companies_table.php`.
- تم حذف migrations الإضافية التالية لأنها أصبحت مدموجة:
  - `2026_07_09_000002_allow_store_device_tokens.php`
  - `2026_07_09_000003_add_eta_fields_to_orders_table.php`
  - `2026_07_09_173102_add_delivery_qr_fields_to_orders_table.php`
  - `2026_07_10_000003_drop_legacy_company_3d_columns.php`

> هذه النسخة مناسبة أكثر للتشغيل على قاعدة جديدة باستخدام `migrate:fresh --seed`.

## Delivery fee

أضيف نظام أجرة التوصيل الزائدة:

- كل شركة تحدد:
  - `delivery_radius_km`
  - `extra_delivery_fee_per_km`
- عند إنشاء الطلب يحسب Laravel المسافة بين الشركة والمتجر.
- إذا المسافة أكبر من الحد، يحسب:
  - `extra_delivery_km = ceil(distance - radius)`
  - `extra_delivery_fee = extra_delivery_km * extra_delivery_fee_per_km`
- يتم حفظ القيم على الطلب حتى لا تتغير إذا عدلت الشركة إعداداتها لاحقاً.

## QR delivery confirmation

- الطلب يحتوي `delivery_qr_code`.
- السائق لا يستطيع تأكيد التسليم عبر endpoint القديم إلا بإرسال `qr_code`.
- Endpoint التأكيد الرئيسي:
  - `POST /api/driver/orders/{order}/confirm-delivery`
  - body: `{ "qr_code": "..." }`

## Seeder

`DatabaseSeeder` صار يعطي بيانات منظمة:

- شركات حقيقية الأسماء مع لوجو.
- متاجر بإحداثيات داخل/خارج نطاق بعض الشركات.
- سائقين وسيارات.
- باقات وميزات اشتراك.
- منتجات عربية مصنفة.
- خصومات كمية.
- صور منتجات وإعلانات من assets محلية.
- طلبات بحالات مختلفة مع QR ورسوم توصيل إضافية ومدفوعات.

الأصول موجودة داخل:

- `database/seeders/assets/companies`
- `database/seeders/assets/products`
- `database/seeders/assets/ads`
- `database/seeders/assets/models`

## Recommended commands

```bash
php artisan storage:link
php artisan migrate:fresh --seed
php artisan optimize:clear
```
