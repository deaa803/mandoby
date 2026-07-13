لوحة Filament الكاملة — نسخة إبداعية مع الحذف

المحتويات:
- جميع ملفات Resources الأصلية الموجودة في الملف الذي أرسلته، بدون حذف النماذج أو المخططات أو صفحات العرض والتعديل.
- زر حذف فردي بالعربية داخل جداول الموارد الرئيسية.
- تأكيد واضح قبل الحذف، مع تنبيه بأن العلاقات التابعة قد تتأثر وفق قاعدة البيانات.
- الإبقاء على إجراءات الفصل Detach داخل العلاقات Many-to-Many بدل حذف السجل المشترك.
- لوحة تحكم جديدة: ترويسة إبداعية، ملخص اليوم، إضافة سريعة، أربع بطاقات رئيسية، مخطط صغير، أرباح الشركات، أحدث الطلبات، ومؤشرات تشغيلية.
- الوضع الليلي والنهاري.
- العربية افتراضيًا مع زر English.
- إيقاف التحديث التلقائي للبطاقات والمخطط لمنع نافذة Page Expired المتكررة.

عدد الملفات التي أضيف لها حذف فردي: 16

طريقة التركيب الآمنة:
1) ضع ملف Filament_Creative_Full_Ready.zip في جذر مشروع Laravel، بجانب artisan.
2) نفّذ:

   Expand-Archive -Path ".\Filament_Creative_Full_Ready.zip" -DestinationPath ".\filament_update" -Force

3) خذ نسخة احتياطية:

   Copy-Item app\Filament app\Filament_backup -Recurse -Force
   Copy-Item app\Providers\Filament\AdminPanelProvider.php app\Providers\Filament\AdminPanelProvider.backup.php -Force

4) انسخ الملفات من المجلد المفكوك:

   Copy-Item .\filament_update\app\* .\app -Recurse -Force
   Copy-Item .\filament_update\resources\* .\resources -Recurse -Force

5) نظف الكاش:

   php artisan optimize:clear
   composer dump-autoload

6) أوقف php artisan serve وشغله من جديد، ثم Ctrl+F5.

مهم:
- الحذف هنا حقيقي وليس إخفاءً. خذ نسخة احتياطية من قاعدة البيانات قبل تجربة حذف الشركات أو المتاجر أو الطلبات، لأن migrations الموجودة في المشروع تحتوي على علاقات cascade في عدة جداول.
- لم يتم تعديل Models أو migrations أو Controllers أو قاعدة البيانات.
