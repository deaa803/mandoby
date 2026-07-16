<?php

namespace App\Filament\Resources\PlatformCommissionPayments\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PlatformCommissionPaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name_company')
                    ->searchable()
                    ->sortable()
                    ->label('الشركة'),

                TextColumn::make('amount')
                    ->money('SYP')
                    ->sortable()
                    ->label('المبلغ'),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'بانتظار المراجعة',
                        'confirmed' => 'مؤكد',
                        'rejected' => 'مرفوض',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->label('الحالة'),

                TextColumn::make('payment_method')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'cash' => 'نقدي',
                        'bank_transfer' => 'تحويل بنكي',
                        'electronic_wallet' => 'محفظة إلكترونية',
                        'other' => 'طريقة أخرى',
                        default => '-',
                    })
                    ->label('طريقة الدفع'),

                TextColumn::make('reference_number')
                    ->placeholder('-')
                    ->searchable()
                    ->label('رقم المرجع'),

                TextColumn::make('submission_source')
                    ->formatStateUsing(fn (string $state): string => $state === 'company' ? 'الشركة' : 'الإدارة')
                    ->badge()
                    ->label('المصدر'),

                TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable()
                    ->label('تاريخ الدفع'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('تاريخ الإنشاء'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'بانتظار المراجعة',
                        'confirmed' => 'مؤكد',
                        'rejected' => 'مرفوض',
                    ])
                    ->label('الحالة'),

                SelectFilter::make('company_id')
                    ->relationship('company', 'name_company')
                    ->searchable()
                    ->preload()
                    ->label('الشركة'),
            ])
            ->recordActions([
                ViewAction::make()->label('عرض')->icon('heroicon-o-eye'),
                EditAction::make()->label('تعديل')->icon('heroicon-o-pencil-square'),
                DeleteAction::make()
                    ->label('حذف')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد حذف الدفعة')
                    ->modalDescription('سيؤدي حذف الدفعة المؤكدة إلى إعادة المبلغ على مستحقات الشركة.')
                    ->modalSubmitActionLabel('نعم، احذف')
                    ->modalCancelActionLabel('إلغاء')
                    ->successNotificationTitle('تم حذف الدفعة بنجاح'),
            ]);
    }
}
