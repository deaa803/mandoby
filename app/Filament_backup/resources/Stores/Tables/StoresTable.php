<?php

namespace App\Filament\Resources\Stores\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name_store')->label('اسم المتجر')->searchable()->sortable(),
                TextColumn::make('user.name')->label('صاحب المتجر')->searchable()->sortable(),
                TextColumn::make('user.email')->label('البريد الإلكتروني')->searchable(),
                TextColumn::make('activity_type')->label('نوع النشاط')->searchable(),
                TextColumn::make('orders_count')->counts('orders')->label('عدد الطلبات'),
                TextColumn::make('created_at')->dateTime()->label('تاريخ الإنشاء')->toggleable(isToggledHiddenByDefault: true)->sortable(),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
