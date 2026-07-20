<?php

namespace App\Filament\Resources\ProductDetails\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductDetailInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('product.name')->label('المنتج'),
                TextEntry::make('company.name_company')->label('الشركة'),
                TextEntry::make('category.name')->label('التصنيف'),
                TextEntry::make('price')->numeric(decimalPlaces: 2)->label('السعر'),
                TextEntry::make('min_order_quantity')->numeric()->label('الحد الأدنى للطلب'),
                TextEntry::make('package_weight_kg')->numeric(decimalPlaces: 3)->suffix(' كغ')->label('وزن الطرد'),
                TextEntry::make('discount.quantity')->numeric()->placeholder('-')->label('كمية الخصم'),
                TextEntry::make('discount.discount_percentage')->suffix('%')->placeholder('-')->label('نسبة الخصم'),
                TextEntry::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'available' ? 'متوفر' : 'غير متوفر')
                    ->color(fn (string $state): string => $state === 'available' ? 'success' : 'danger')
                    ->label('الحالة'),
                TextEntry::make('has_3d_model')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'متوفر' : 'غير متوفر')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->label('مودل 3D'),
                ImageEntry::make('images.url')
                    ->disk('public')
                    ->label('صور المنتج'),
                TextEntry::make('created_at')->dateTime()->label('تاريخ الإنشاء'),
                TextEntry::make('updated_at')->dateTime()->label('آخر تحديث'),
            ]);
    }
}
