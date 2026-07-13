<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('رقم الطلب')->sortable(),
                TextColumn::make('store.name_store')->label('المتجر')->searchable()->sortable(),
                TextColumn::make('total_price')->numeric(decimalPlaces: 2)->label('الإجمالي')->sortable(),
                TextColumn::make('paid_amount')->numeric(decimalPlaces: 2)->label('المدفوع')->sortable(),
                TextColumn::make('remaining_amount')->numeric(decimalPlaces: 2)->label('المتبقي')->sortable(),
                TextColumn::make('date')->date()->label('التاريخ')->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'قيد الانتظار',
                        'preparing' => 'قيد التجهيز',
                        'delivering' => 'قيد التوصيل',
                        'delivered' => 'مكتمل',
                        'cancelled' => 'ملغي',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'preparing' => 'info',
                        'delivering' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->label('الحالة'),
                TextColumn::make('driver.user.name')->label('السائق')->searchable()->placeholder('-'),
                TextColumn::make('commission')->numeric(decimalPlaces: 2)->label('العمولة')->sortable(),
                TextColumn::make('created_at')->dateTime()->label('تاريخ الإنشاء')->toggleable(isToggledHiddenByDefault: true)->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'preparing' => 'قيد التجهيز',
                        'delivering' => 'قيد التوصيل',
                        'delivered' => 'مكتمل',
                        'cancelled' => 'ملغي',
                    ])
                    ->label('الحالة'),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
