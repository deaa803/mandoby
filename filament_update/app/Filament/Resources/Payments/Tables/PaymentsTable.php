<?php

namespace App\Filament\Resources\Payments\Tables;

use Filament\Actions\DeleteAction;
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
                DeleteAction::make()
                                    ->label('حذف')
                                    ->icon('heroicon-o-trash')
                                    ->color('danger')
                                    ->modalHeading('تأكيد حذف السجل')
                                    ->modalDescription('هل أنت متأكد من حذف هذا السجل؟ قد يؤدي الحذف إلى إزالة البيانات المرتبطة به حسب علاقات قاعدة البيانات، ولا يمكن التراجع عن العملية.')
                                    ->modalSubmitActionLabel('نعم، احذف')
                                    ->modalCancelActionLabel('إلغاء')
                                    ->successNotificationTitle('تم حذف السجل بنجاح'),
            ]);
    }
}
