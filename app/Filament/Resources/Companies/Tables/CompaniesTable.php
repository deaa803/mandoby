<?php

namespace App\Filament\Resources\Companies\Tables;

use App\Filament\Resources\PlatformCommissionPayments\PlatformCommissionPaymentResource;
use App\Models\Company;
use App\Services\PlatformProfitService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
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

                TextColumn::make('platform_accrued')
                    ->state(fn (Company $record): float => (float) app(PlatformProfitService::class)
                        ->companyStatement($record)['accrued_commission'])
                    ->money('SYP')
                    ->label('المستحق للمنصة'),

                TextColumn::make('platform_confirmed')
                    ->state(fn (Company $record): float => (float) app(PlatformProfitService::class)
                        ->companyStatement($record)['confirmed_platform_payments'])
                    ->money('SYP')
                    ->color('success')
                    ->label('المدفوع للمنصة'),

                TextColumn::make('platform_remaining')
                    ->state(fn (Company $record): float => (float) app(PlatformProfitService::class)
                        ->companyStatement($record)['remaining_amount'])
                    ->money('SYP')
                    ->color(fn ($state): string => (float) $state > 0 ? 'danger' : 'success')
                    ->label('المتبقي'),

                TextColumn::make('platform_status')
                    ->state(fn (Company $record): string => (string) app(PlatformProfitService::class)
                        ->companyStatement($record)['status'])
                    ->formatStateUsing(fn (string $state): string => app(PlatformProfitService::class)->statusLabel($state))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'partial' => 'warning',
                        'unpaid' => 'danger',
                        'overpaid' => 'info',
                        default => 'gray',
                    })
                    ->label('حالة التسديد'),

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
                    ->label('حد التوصيل')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('currentSubscription.plan.name')
                    ->label('الاشتراك')
                    ->placeholder('-'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('تاريخ الإنشاء'),
            ])
            ->recordActions([
                Action::make('registerPlatformPayment')
                    ->label('تسجيل دفعة للمنصة')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (Company $record): bool =>
                        (float) app(PlatformProfitService::class)
                            ->companyStatement($record)['remaining_amount'] > 0)
                    ->url(fn (Company $record): string => PlatformCommissionPaymentResource::getUrl('create', [
                        'company_id' => $record->id,
                    ])),

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
