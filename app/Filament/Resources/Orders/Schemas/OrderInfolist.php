<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')->label('رقم الطلب'),
                TextEntry::make('store.name_store')->label('المتجر'),
                TextEntry::make('date')->date()->label('تاريخ الطلب'),
                TextEntry::make('status')->badge()->label('الحالة'),
                TextEntry::make('driver.user.name')->label('السائق')->placeholder('-'),
                TextEntry::make('driver.car.vehicle_type')->label('السيارة المرتبطة')->placeholder('-'),
                TextEntry::make('total_weight_kg')->numeric(decimalPlaces: 3)->suffix(' كغ')->label('وزن الطلب'),
                TextEntry::make('required_load_kg')->numeric(decimalPlaces: 3)->suffix(' كغ')->label('الحمولة المطلوبة مع الأمان'),
                TextEntry::make('driver_assignment_method')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'smart' => 'اقتراح ذكي',
                        'manual' => 'اختيار يدوي',
                        default => '-',
                    })
                    ->badge()
                    ->label('طريقة التعيين'),
                TextEntry::make('total_price')->numeric(decimalPlaces: 2)->label('الإجمالي'),
                TextEntry::make('paid_amount')->numeric(decimalPlaces: 2)->label('المدفوع'),
                TextEntry::make('remaining_amount')->numeric(decimalPlaces: 2)->label('المتبقي'),
                TextEntry::make('commission')->numeric(decimalPlaces: 2)->label('عمولة المنصة من الدفعات'),
                TextEntry::make('created_at')->dateTime()->label('تاريخ الإنشاء'),
                TextEntry::make('updated_at')->dateTime()->label('آخر تحديث'),
            ]);
    }
}
