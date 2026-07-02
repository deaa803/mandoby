<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')->label('اسم المنتج'),
                TextEntry::make('description')->label('الوصف')->placeholder('-')->columnSpanFull(),
                TextEntry::make('min_order_quantity')->label('الحد الأدنى للطلب'),
                TextEntry::make('details_count')->state(fn ($record): int => $record->details()->count())->label('عدد عروض الشركات'),
                TextEntry::make('created_at')->dateTime()->label('تاريخ الإنشاء'),
                TextEntry::make('updated_at')->dateTime()->label('آخر تحديث'),
            ]);
    }
}
