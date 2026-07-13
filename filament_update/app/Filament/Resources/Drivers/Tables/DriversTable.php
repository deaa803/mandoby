<?php

namespace App\Filament\Resources\Drivers\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DriversTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('السائق')->searchable()->sortable(),
                TextColumn::make('user.email')->label('البريد الإلكتروني')->searchable(),
                TextColumn::make('car.company.name_company')->label('الشركة')->searchable()->sortable(),
                TextColumn::make('car.plate_number')->label('رقم اللوحة')->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'available' => 'متاح',
                        'busy' => 'مشغول',
                        'offline' => 'غير متصل',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'busy' => 'warning',
                        'offline' => 'gray',
                        default => 'gray',
                    })
                    ->label('الحالة'),
                TextColumn::make('last_location_at')->dateTime()->label('آخر تحديث للموقع')->placeholder('-')->sortable(),
                TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault: true)->label('تاريخ الإنشاء'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'available' => 'متاح',
                        'busy' => 'مشغول',
                        'offline' => 'غير متصل',
                    ])
                    ->label('الحالة'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                                    ->label('حذف')
                                    ->icon('heroicon-o-trash')
                                    ->color('danger')
                                    ->modalHeading('تأكيد حذف السجل')
                                    ->modalDescription('هل أنت متأكد من حذف هذا السجل؟ قد يؤدي الحذف إلى إزالة البيانات المرتبطة به حسب علاقات قاعدة البيانات، ولا يمكن التراجع عن العملية.')
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
