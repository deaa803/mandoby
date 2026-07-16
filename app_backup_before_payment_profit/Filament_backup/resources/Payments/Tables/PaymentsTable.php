<?php

namespace App\Filament\Resources\Payments\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.id')
                    ->label('رقم الطلب')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('order.store.name_store')
                    ->label('المتجر')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount')
                    ->numeric(decimalPlaces: 2)
                    ->label('المبلغ')
                    ->sortable(),

                TextColumn::make('paid_at')
                    ->dateTime()
                    ->label('تاريخ الدفع')
                    ->sortable(),

                TextColumn::make('note')
                    ->label('الملاحظة')
                    ->limit(40)
                    ->placeholder('-'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->label('تاريخ الإنشاء')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
