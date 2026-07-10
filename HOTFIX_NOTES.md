# Hotfix Notes

هذه النسخة تلغي الاعتماد القديم على حقول 3D داخل جدول الشركات، وتثبت أن مصدر صلاحيات 3D والتقارير صار من نظام الاشتراكات فقط.

## تم تعديل/إضافة
- حذف `has_3d_access` و `model_3d_expires_at` من `Company.php`.
- حذف القيم القديمة من الـ seeder.
- إضافة migration لحذف أعمدة 3D القديمة من جدول `companies` عند التشغيل على قاعدة بيانات موجودة.
- إبقاء فحص 3D عبر `Company::hasActiveFeature('3d_models')`.
- إبقاء فحص التقارير عبر `Company::hasActiveFeature('advanced_reports')`.

## أوامر التشغيل
```bash
php artisan migrate
php artisan subscriptions:check-expiry
```
