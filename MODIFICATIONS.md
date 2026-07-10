# التعديلات المنفذة

## خصومات الكمية
- إضافة جدول `product_discounts`.
- إضافة موديل `ProductDiscount`.
- ربط الخصم مع `ProductDetail` بعلاقة `discount`.
- إضافة حقول الخصم في لوحة Filament داخل تفاصيل المنتج:
  - يوجد خصم.
  - كمية تطبيق الخصم.
  - نسبة الخصم.
- تعديل `OrderController` ليحسب الخصم من قاعدة البيانات بدل النسب الثابتة القديمة.
- دعم حقول API عند إنشاء/تعديل المنتج:
  - `has_discount`
  - `discount_quantity`
  - `discount_percentage`

## الاشتراكات
- إضافة جداول:
  - `subscription_plans`
  - `subscription_features`
  - `subscription_plan_features`
  - `company_subscriptions`
- إضافة موديلات:
  - `SubscriptionPlan`
  - `SubscriptionFeature`
  - `CompanySubscription`
- فصل الاشتراكات عن جدول `features` الخاص بمواصفات المنتجات.
- إضافة موارد Filament لإدارة:
  - باقات الاشتراك.
  - ميزات الاشتراك.
  - اشتراكات الشركات.
- إضافة فحص الميزات عبر `Company::hasActiveFeature()`.

## 3D والتقارير المدفوعة
- تعديل إنشاء موديل 3D ليعتمد على ميزة الاشتراك `3d_models`.
- إضافة API لحالة الاشتراك:
  - `GET /api/company/subscription`
- إضافة API للتقارير المدفوعة:
  - `GET /api/company/reports`
- التقارير الموجودة:
  - ملخص المبيعات.
  - المبيعات اليومية.
  - المبيعات الشهرية.
  - أكثر المنتجات مبيعاً.
  - أقل المنتجات حركة.
  - تقرير صافي المبيعات بعد الخصومات.
  - تأثير الخصومات.
  - حركة المخزون خروج فقط حسب المبيعات.
  - أكثر العملاء شراءً.

## إشعارات قرب انتهاء الاشتراك
- إضافة أمر Artisan:
  - `php artisan subscriptions:check-expiry`
- جدولة الفحص يومياً الساعة 09:00 من `routes/console.php`.
- الإشعارات:
  - قبل 7 أيام.
  - قبل 3 أيام.
  - بعد انتهاء الاشتراك.
- استخدام `AppNotificationService` و `FirebaseNotificationService` الموجودين بالمشروع.

## بعد تنزيل النسخة
شغل:

```bash
php artisan migrate
php artisan db:seed
php artisan subscriptions:check-expiry
```

وللتشغيل التلقائي على السيرفر لازم يكون Laravel scheduler شغال:

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```
