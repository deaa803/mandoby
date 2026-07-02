<?php

namespace App\Filament\Resources\Companies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->disk('public')
                    ->label('الشعار')
                    ->circular(),

                TextColumn::make('name_company')
                    ->label('اسم الشركة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('المالك')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('product_details_count')
                    ->counts('productDetails')
                    ->label('المنتجات'),

                TextColumn::make('drivers_count')
                    ->counts('drivers')
                    ->label('السائقين'),

                TextColumn::make('stores_count')
                    ->counts('stores')
                    ->label('المتاجر'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('تاريخ الإنشاء'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
