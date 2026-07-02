<?php

namespace App\Filament\Resources\ProductDetails\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductDetailsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('first_image')
                    ->state(fn ($record): ?string => $record->images->first()?->url)
                    ->disk('public')
                    ->label('الصورة'),

                TextColumn::make('product.name')
                    ->label('المنتج')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('company.name_company')
                    ->label('الشركة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('التصنيف')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('price')
                    ->numeric(decimalPlaces: 2)
                    ->label('السعر')
                    ->sortable(),

                TextColumn::make('min_order_quantity')
                    ->numeric()
                    ->label('الحد الأدنى')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'available' => 'متوفر',
                        'unavailable' => 'غير متوفر',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'unavailable' => 'danger',
                        default => 'gray',
                    })
                    ->label('الحالة'),

                IconColumn::make('has_3d_model')
                    ->boolean()
                    ->label('مودل 3D'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('تاريخ الإنشاء'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'available' => 'متوفر',
                        'unavailable' => 'غير متوفر',
                    ])
                    ->label('الحالة'),
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
