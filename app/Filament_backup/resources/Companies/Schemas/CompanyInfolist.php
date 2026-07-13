<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CompanyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ImageEntry::make('logo')->disk('public')->label('الشعار'),
                TextEntry::make('name_company')->label('اسم الشركة'),
                TextEntry::make('description')->label('الوصف')->placeholder('-')->columnSpanFull(),
                TextEntry::make('user.name')->label('المالك'),
                TextEntry::make('user.email')->label('البريد الإلكتروني'),
                TextEntry::make('user.phone')->label('رقم الهاتف')->placeholder('-'),
                TextEntry::make('delivery_radius_km')->suffix(' كم')->label('حد التوصيل العادي'),
                TextEntry::make('extra_delivery_fee_per_km')->money('SYP')->label('أجرة الكيلومتر الزائد'),
                TextEntry::make('currentSubscription.plan.name')->label('الاشتراك الحالي')->placeholder('-'),
                TextEntry::make('currentSubscription.end_date')->dateTime()->label('انتهاء الاشتراك')->placeholder('-'),
                TextEntry::make('created_at')->dateTime()->label('تاريخ الإنشاء'),
                TextEntry::make('updated_at')->dateTime()->label('آخر تحديث'),
            ]);
    }
}
