<?php

namespace App\Filament\Resources\CompanyCars\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CompanyCarsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name_company')
                    ->label('الشركة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('vehicle_type')
                    ->label('نوع السيارة')
                    ->searchable(),
                TextColumn::make('plate_number')
                    ->label('رقم اللوحة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('driver_name')
                    ->label('اسم السائق')
                    ->searchable(),
                TextColumn::make('driver.user.email')
                    ->label('بريد السائق')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('driver.status')
                    ->badge()
                    ->label('حالة السائق')
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('تاريخ الإنشاء'),
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
