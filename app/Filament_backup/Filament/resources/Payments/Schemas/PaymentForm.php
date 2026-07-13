<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('order_id')
                    ->relationship('order', 'id')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('رقم الطلب'),

                TextInput::make('amount')
                    ->numeric()
                    ->minValue(0.01)
                    ->required()
                    ->label('المبلغ المدفوع'),

                DateTimePicker::make('paid_at')
                    ->default(now())
                    ->required()
                    ->label('تاريخ ووقت الدفع'),

                Textarea::make('note')
                    ->rows(3)
                    ->nullable()
                    ->columnSpanFull()
                    ->label('ملاحظة'),
            ]);
    }
}
