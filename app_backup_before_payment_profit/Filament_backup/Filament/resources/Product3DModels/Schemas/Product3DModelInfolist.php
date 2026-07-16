<?php

namespace App\Filament\Resources\Product3DModels\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class Product3DModelInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('productDetail.product.name')->label('المنتج'),
                TextEntry::make('company.name_company')->label('الشركة'),
                ImageEntry::make('source_image')->disk('public')->label('الصورة المصدر'),
                ImageEntry::make('thumbnail')->disk('public')->label('صورة المعاينة')->placeholder('-'),
                TextEntry::make('model_file')
                    ->label('ملف المودل')
                    ->url(fn ($record): ?string => $record->model_file_url)
                    ->openUrlInNewTab()
                    ->placeholder('-'),
                TextEntry::make('status')->badge()->label('الحالة'),
                TextEntry::make('progress')->suffix('%')->label('نسبة التقدم'),
                TextEntry::make('error_message')->label('رسالة الخطأ')->placeholder('-')->columnSpanFull(),
                KeyValueEntry::make('metadata')->label('بيانات إضافية')->columnSpanFull(),
                TextEntry::make('generated_at')->dateTime()->label('تاريخ التوليد')->placeholder('-'),
                TextEntry::make('created_at')->dateTime()->label('تاريخ الإنشاء'),
                TextEntry::make('updated_at')->dateTime()->label('آخر تحديث'),
            ]);
    }
}
