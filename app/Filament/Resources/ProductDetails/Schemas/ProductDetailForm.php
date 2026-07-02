<?php

namespace App\Filament\Resources\ProductDetails\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductDetailForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('المنتج'),

                Select::make('company_id')
                    ->relationship('company', 'name_company')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('الشركة'),

                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('التصنيف'),

                TextInput::make('price')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required()
                    ->label('السعر'),

                TextInput::make('min_order_quantity')
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->default(1)
                    ->required()
                    ->label('الحد الأدنى للطلب'),

                Select::make('status')
                    ->options([
                        'available' => 'متوفر',
                        'unavailable' => 'غير متوفر',
                    ])
                    ->default('available')
                    ->required()
                    ->native(false)
                    ->label('حالة المنتج'),

                Repeater::make('images')
                    ->relationship('images')
                    ->schema([
                        FileUpload::make('url')
                            ->image()
                            ->disk('public')
                            ->directory('products')
                            ->visibility('public')
                            ->required()
                            ->label('الصورة'),
                    ])
                    ->defaultItems(1)
                    ->minItems(1)
                    ->reorderable(false)
                    ->columnSpanFull()
                    ->label('صور المنتج'),
            ]);
    }
}
