<?php

namespace App\Filament\Resources\Advertisements\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AdvertisementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('company.name_company')->label('الشركة'),
                TextEntry::make('productDetail.product.name')->label('المنتج')->placeholder('-'),
                TextEntry::make('title')->label('العنوان'),
                TextEntry::make('description')->label('الوصف')->placeholder('-')->columnSpanFull(),
                ImageEntry::make('image')->disk('public')->label('الصورة'),
                TextEntry::make('price')->numeric(decimalPlaces: 2)->label('السعر'),
                TextEntry::make('status')->badge()->label('الحالة'),
                TextEntry::make('starts_at')->dateTime()->label('يبدأ في')->placeholder('-'),
                TextEntry::make('ends_at')->dateTime()->label('ينتهي في')->placeholder('-'),
                TextEntry::make('created_at')->dateTime()->label('تاريخ الإنشاء'),
                TextEntry::make('updated_at')->dateTime()->label('آخر تحديث'),
            ]);
    }
}
