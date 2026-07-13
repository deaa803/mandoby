<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('store_id')
                    ->relationship('store', 'name_store')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('المتجر'),

                DatePicker::make('date')
                    ->default(now())
                    ->required()
                    ->label('تاريخ الطلب'),

                Select::make('driver_id')
                    ->relationship('driver', 'id')
                    ->getOptionLabelFromRecordUsing(
                        fn (Model $record): string => ($record->user?->name ?? 'سائق') . ' - #' . $record->id,
                    )
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->label('السائق'),

                Select::make('status')
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'preparing' => 'قيد التجهيز',
                        'delivering' => 'قيد التوصيل',
                        'delivered' => 'مكتمل',
                        'cancelled' => 'ملغي',
                    ])
                    ->default('pending')
                    ->required()
                    ->native(false)
                    ->label('حالة الطلب'),

                TextInput::make('commission')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required()
                    ->label('العمولة'),

                TextInput::make('total_price')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false)
                    ->label('إجمالي الطلب'),

                TextInput::make('paid_amount')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false)
                    ->label('المبلغ المدفوع'),

                TextInput::make('remaining_amount')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false)
                    ->label('المبلغ المتبقي'),
            ]);
    }
}
