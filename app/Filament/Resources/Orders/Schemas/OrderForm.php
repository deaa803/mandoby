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
                        fn (Model $record): string => ($record->user?->name ?? 'سائق')
                            . ' — ' . ($record->car?->vehicle_type ?? 'بدون سيارة')
                            . ' — ' . number_format((float) ($record->car?->max_load_kg ?? 0), 0) . ' كغ',
                    )
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('استخدم زر «اقتراح سائق ذكي» أو «اختيار سائق يدويًا» من جدول الطلبات.')
                    ->label('السائق المعين'),

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
                    ->default(0)
                    ->disabled()
                    ->dehydrated()
                    ->helperText('تُحسب تلقائيًا بنسبة 2٪ من مجموع الدفعات الفعلية للطلب.')
                    ->label('عمولة المنصة من الدفعات'),

                TextInput::make('total_weight_kg')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false)
                    ->suffix('كغ')
                    ->label('وزن الطلب'),

                TextInput::make('required_load_kg')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false)
                    ->suffix('كغ')
                    ->label('الحمولة المطلوبة مع الأمان'),

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
