<?php

namespace App\Filament\Resources\ShippingOffices\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShippingOfficesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('اسم المكتب')->searchable()->sortable(),
                TextColumn::make('user.email')->label('البريد الإلكتروني')->searchable(),
                TextColumn::make('user.phone')->label('رقم الهاتف')->placeholder('-'),
                TextColumn::make('created_at')->dateTime()->label('تاريخ الإنشاء')->sortable(),
            ])
            ->recordActions([ViewAction::make(), EditAction::make(),
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
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
