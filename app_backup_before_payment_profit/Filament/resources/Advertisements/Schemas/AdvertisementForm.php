<?php

namespace App\Filament\Resources\Advertisements\Schemas;

use App\Models\ProductDetail;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class AdvertisementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->relationship('company', 'name_company')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (Set $set): void {
                        // عند تغيير الشركة، نفرغ المنتج السابق حتى لا يبقى
                        // منتج تابع لشركة أخرى داخل العرض.
                        $set('product_detail_id', null);
                    })
                    ->required()
                    ->label('الشركة'),

                Select::make('product_detail_id')
                    ->options(function (Get $get): array {
                        $companyId = $get('company_id');

                        if (blank($companyId)) {
                            return [];
                        }

                        return ProductDetail::query()
                            ->where('company_id', $companyId)
                            ->with('product:id,name')
                            ->orderBy('id')
                            ->get()
                            ->mapWithKeys(function (ProductDetail $productDetail): array {
                                $productName = $productDetail->product?->name ?? 'منتج بدون اسم';
                                $price = number_format((float) $productDetail->price, 2);

                                return [
                                    $productDetail->getKey() => "{$productName} - السعر: {$price} - رقم #{$productDetail->getKey()}",
                                ];
                            })
                            ->all();
                    })
                    ->searchable()
                    ->preload()
                    ->disabled(fn (Get $get): bool => blank($get('company_id')))
                    ->placeholder('اختر الشركة أولًا')
                    ->noOptionsMessage('لا توجد منتجات تابعة للشركة المختارة')
                    ->helperText('لا تظهر هنا إلا المنتجات التابعة للشركة التي اخترتها في الحقل السابق.')
                    ->nullable()
                    ->label('المنتج المرتبط'),

                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->label('عنوان العرض'),

                Textarea::make('description')
                    ->rows(4)
                    ->nullable()
                    ->columnSpanFull()
                    ->label('الوصف'),

                FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->directory('advertisements')
                    ->visibility('public')
                    ->required()
                    ->label('صورة العرض'),

                TextInput::make('price')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required()
                    ->label('سعر العرض'),

                Select::make('status')
                    ->options([
                        'pending' => 'قيد المراجعة',
                        'active' => 'نشط',
                        'rejected' => 'مرفوض',
                        'expired' => 'منتهي',
                    ])
                    ->default('active')
                    ->required()
                    ->native(false)
                    ->label('الحالة'),

                DateTimePicker::make('starts_at')
                    ->label('تاريخ بدء العرض'),

                DateTimePicker::make('ends_at')
                    ->afterOrEqual('starts_at')
                    ->label('تاريخ انتهاء العرض'),
            ]);
    }
}
