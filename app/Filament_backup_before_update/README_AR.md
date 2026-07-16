# لوحة Filament المعدلة

## الملفات المضافة/المعدلة

- `app/Filament/Widgets/DashboardHeader.php`
- `app/Filament/Widgets/StatsOverview.php`
- `app/Filament/Widgets/SalesChart.php`
- `app/Filament/Widgets/CompanyProfitOverview.php`
- `resources/views/filament/widgets/dashboard-header.blade.php`
- `resources/views/filament/widgets/company-profit-overview.blade.php`

## التركيب

انسخ مجلدي `app` و`resources` إلى جذر مشروع Laravel، مع الموافقة على استبدال ملفي:

- `StatsOverview.php`
- `SalesChart.php`

ثم نفّذ:

```bash
php artisan optimize:clear
php artisan view:clear
```

Widgets الموجودة داخل `App\\Filament\\Widgets` تُكتشف تلقائيًا في أغلب إعدادات Filament. إن كانت صفحة Dashboard عندك تسجل Widgets يدويًا، أضف:

```php
use App\Filament\Widgets\DashboardHeader;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\SalesChart;
use App\Filament\Widgets\CompanyProfitOverview;

protected function getHeaderWidgets(): array
{
    return [
        DashboardHeader::class,
        StatsOverview::class,
        SalesChart::class,
        CompanyProfitOverview::class,
    ];
}
```

## ما تمت إضافته

- تصميم نهاري كريمي/ذهبي قريب من واجهة تطبيق المورد.
- تصميم ليلي كحلي قريب من صورة لوحة التحكم المرجعية.
- زر تبديل الوضع الليلي والنهاري مع حفظ الاختيار في المتصفح.
- زر عربي/English لمحتوى لوحة القيادة.
- بطاقات للشركات والمتاجر والطلبات والأرباح والمدفوعات والنماذج ثلاثية الأبعاد.
- مخطط نشاط آخر 30 يومًا.
- قائمة توضح عمولة المنصة المتوقعة من كل شركة.
- الطلبات الملغاة مستبعدة من قيمة المبيعات والأرباح.

## العملة

تُقرأ العملة من:

```php
config('app.currency', 'USD')
```

يمكن إضافة القيمة إلى `config/app.php`:

```php
'currency' => env('APP_CURRENCY', 'USD'),
```

ثم في `.env` مثلًا:

```env
APP_CURRENCY=USD
```

## ملاحظة اللغة

المبدّل يترجم محتوى Widgets المضافة. ترجمة القائمة الجانبية وأسماء Resources بالكامل تعتمد على أسماء التنقل الموجودة في كل Resource أو على نظام ترجمة Filament المستخدم في المشروع.
