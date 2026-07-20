<?php

namespace App\Filament\Resources\CompanyCars\Tables;

use Filament\Actions\DeleteAction;
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

                TextColumn::make('max_load_kg')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' كغ')
                    ->label('الحمولة القصوى')
                    ->sortable(),
                TextColumn::make('driver.user.name')
                    ->label('اسم السائق')
                    ->searchable()
                    ->placeholder('-'),
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
                ViewAction::make()
                    ->label('عرض')
                    ->icon('heroicon-o-eye'),

                EditAction::make()
                    ->label('تعديل')
                    ->icon('heroicon-o-pencil-square'),

                DeleteAction::make()
                    ->label('حذف')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد الحذف')
                    ->modalDescription('هل أنت متأكد من حذف هذا السجل؟ لا يمكن التراجع عن العملية بعد تنفيذها.')
                    ->modalSubmitActionLabel('نعم، احذف')
                    ->modalCancelActionLabel('إلغاء')
                    ->successNotificationTitle('تم حذف السجل بنجاح'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
