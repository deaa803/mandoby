<?php

namespace App\Filament\Resources\Advertisements\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AdvertisementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->disk('public')->label('الصورة'),
                TextColumn::make('title')->label('العنوان')->searchable()->sortable(),
                TextColumn::make('company.name_company')->label('الشركة')->searchable()->sortable(),
                TextColumn::make('productDetail.product.name')->label('المنتج')->searchable()->placeholder('-'),
                TextColumn::make('price')->numeric(decimalPlaces: 2)->label('السعر')->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'قيد المراجعة',
                        'active' => 'نشط',
                        'rejected' => 'مرفوض',
                        'expired' => 'منتهي',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'active' => 'success',
                        'rejected' => 'danger',
                        'expired' => 'gray',
                        default => 'gray',
                    })
                    ->label('الحالة'),
                TextColumn::make('starts_at')->dateTime()->label('يبدأ في')->placeholder('-')->sortable(),
                TextColumn::make('ends_at')->dateTime()->label('ينتهي في')->placeholder('-')->sortable(),
                TextColumn::make('created_at')->dateTime()->label('تاريخ الإنشاء')->toggleable(isToggledHiddenByDefault: true)->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'قيد المراجعة',
                        'active' => 'نشط',
                        'rejected' => 'مرفوض',
                        'expired' => 'منتهي',
                    ])
                    ->label('الحالة'),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
