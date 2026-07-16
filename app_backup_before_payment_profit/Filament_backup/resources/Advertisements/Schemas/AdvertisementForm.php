<?php

namespace App\Filament\Resources\Advertisements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

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
                    ->required()
                    ->label('الشركة'),

                Select::make('product_detail_id')
                    ->relationship('productDetail', 'id')
                    ->getOptionLabelFromRecordUsing(
                        fn (Model $record): string => ($record->product?->name ?? 'منتج') . ' - #' . $record->id,
                    )
                    ->searchable()
                    ->preload()
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
