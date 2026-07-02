# تحديث بنية إضافة المنتج

## التغييرات

- أصبح السعر والحد الأدنى للطلب ضمن `product_details` لأنهما يخصان عرض الشركة.
- بقي الاسم والوصف ضمن `products`.
- الشركة تؤخذ حصراً من Sanctum Token.
- عند الإنشاء لا يقبل السيرفر `company_id` أو `product_id` أو `status` من التطبيق.
- إنشاء `Product` و`ProductDetail` وربط الميزات يتم ضمن Transaction واحدة.
- تحديث Filament والـSeeder ليتوافقا مع البنية الجديدة.

## بعد استبدال الملفات

```bash
php artisan migrate
php artisan optimize:clear
php artisan storage:link
```

نفّذ `storage:link` فقط إذا لم يكن الرابط موجوداً مسبقاً.

## مسار الإضافة

```text
POST /api/product-details
Authorization: Bearer TOKEN
```

## الحقول

```json
{
  "product_name": "اسم المنتج",
  "description": "وصف المنتج",
  "category_id": 2,
  "price": 15000,
  "min_order_quantity": 10,
  "features": [
    {
      "feature_id": 1,
      "value": "24 قطعة"
    }
  ]
}
```

الصورة يمكن رفعها مع نفس الطلب باسم `image`، أو بعد الإنشاء عبر:

```text
POST /api/product-details/{productDetailId}/images
```
