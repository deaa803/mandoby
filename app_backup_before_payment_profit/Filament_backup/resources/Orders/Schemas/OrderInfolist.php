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
                TextEntry::make('total_price')->numeric(decimalPlaces: 2)->label('الإجمالي'),
                TextEntry::make('paid_amount')->numeric(decimalPlaces: 2)->label('المدفوع'),
                TextEntry::make('remaining_amount')->numeric(decimalPlaces: 2)->label('المتبقي'),
                TextEntry::make('commission')->numeric(decimalPlaces: 2)->label('العمولة'),
                TextEntry::make('created_at')->dateTime()->label('تاريخ الإنشاء'),
                TextEntry::make('updated_at')->dateTime()->label('آخر تحديث'),
            ]);
    }
}
