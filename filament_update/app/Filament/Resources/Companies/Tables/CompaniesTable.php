<?php

namespace App\Filament\Resources\Companies\Tables;

use Filament\Actions\DeleteAction;
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

                TextColumn::make('delivery_radius_km')
                    ->suffix(' كم')
                    ->label('حد التوصيل'),

                TextColumn::make('extra_delivery_fee_per_km')
                    ->money('SYP')
                    ->label('أجرة الكيلو الزائد')
                    ->toggleable(),

                TextColumn::make('currentSubscription.plan.name')
                    ->label('الاشتراك')
                    ->placeholder('-'),

                TextColumn::make('currentSubscription.end_date')
                    ->dateTime()
                    ->label('ينتهي في')
                    ->placeholder('-'),

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
