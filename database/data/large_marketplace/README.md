# Dataset المتجر الكبير

المجلد مجهز لإضافة:

- 50 شركة متخصصة.
- 20 منتجاً لكل شركة.
- 1000 منتج بالمجموع.
- صورة محلية لكل منتج.
- شعار محلي لكل شركة.
- وزن طرد منطقي بالكيلوغرام لكل منتج.

## التشغيل من داخل مجلد مشروع Laravel

نفّذ المايغريشن أولاً:

```powershell
php artisan migrate
```

ثبّت متطلبات سكربت التنزيل:

```powershell
py -m pip install -r database/dataset_requirements.txt
```

نزّل المنتجات والصور من جديد:

```powershell
py database/download_large_marketplace_products.py --fresh
```

الصور والـ CSV ستُحفظ محلياً داخل:

```text
storage/app/public/imports/large_marketplace_1000
```

بعد اكتمال 1000/1000:

```powershell
php artisan storage:link
php artisan db:seed --class=LargeMarketplaceDatasetSeeder
```

إذا انقطع الإنترنت، أعد تشغيل السكربت بدون `--fresh` حتى يكمل من الموجود:

```powershell
py database/download_large_marketplace_products.py
```

لحذف الداتا التي نزلها السكربت فقط:

```powershell
Remove-Item -Recurse -Force "storage\app\public\imports\large_marketplace_1000" -ErrorAction SilentlyContinue
```
