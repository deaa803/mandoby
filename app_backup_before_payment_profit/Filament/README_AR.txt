لوحة Filament المعدلة — طريقة التركيب
=====================================

هذه النسخة مرتبة بمسارات Laravel الصحيحة، وليست مجلد Filament منفصلًا.

1) خذ نسخة احتياطية من مشروعك.
2) فك ضغط الملف في جذر مشروع Laravel نفسه، حيث يوجد ملف artisan.
3) وافق على دمج/استبدال الملفات داخل:
   app/Filament
   resources/views/filament/widgets
4) نفّذ:
   php artisan optimize:clear
   php artisan view:clear

مهم:
- يجب أن تكون Widgets مكتشفة في AdminPanelProvider عبر:
  ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
- التعديلات مصممة لـ Filament 4 لأن ملفات مشروعك تستخدم Filament\\Schemas وHeroicon.
- زر الوضع الليلي/النهاري وزر العربية/English يعملان من أعلى لوحة القيادة.
- حساب أرباح المنصة يعتمد على orders.commission ويستبعد الطلبات الملغاة.
- جدول الشركات أصبح يعرض عدد طلبات كل شركة وربح المنصة منها.
