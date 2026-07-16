<?php

namespace App\Filament\Resources\Companies\Schemas;

use App\Models\Company;
use App\Services\PlatformProfitService;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CompanyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ImageEntry::make('logo')->disk('public')->label('الشعار'),
                TextEntry::make('name_company')->label('اسم الشركة'),
                TextEntry::make('description')->label('الوصف')->placeholder('-')->columnSpanFull(),
                TextEntry::make('user.name')->label('المالك'),
                TextEntry::make('user.email')->label('البريد الإلكتروني'),
                TextEntry::make('user.phone')->label('رقم الهاتف')->placeholder('-'),

                TextEntry::make('platform_accrued')
                    ->state(fn (Company $record): float => (float) app(PlatformProfitService::class)
                        ->companyStatement($record)['accrued_commission'])
                    ->money('SYP')
                    ->label('أرباح المنصة المستحقة'),

                TextEntry::make('platform_confirmed')
                    ->state(fn (Company $record): float => (float) app(PlatformProfitService::class)
                        ->companyStatement($record)['confirmed_platform_payments'])
                    ->money('SYP')
                    ->label('المدفوع للمنصة'),

                TextEntry::make('platform_pending')
                    ->state(fn (Company $record): float => (float) app(PlatformProfitService::class)
                        ->companyStatement($record)['pending_platform_payments'])
                    ->money('SYP')
                    ->label('بانتظار المراجعة'),

                TextEntry::make('platform_remaining')
                    ->state(fn (Company $record): float => (float) app(PlatformProfitService::class)
                        ->companyStatement($record)['remaining_amount'])
                    ->money('SYP')
                    ->label('المتبقي على الشركة'),

                TextEntry::make('platform_status')
                    ->state(fn (Company $record): string => (string) app(PlatformProfitService::class)
                        ->companyStatement($record)['status'])
                    ->formatStateUsing(fn (string $state): string => app(PlatformProfitService::class)->statusLabel($state))
                    ->badge()
                    ->label('حالة التسديد'),

                TextEntry::make('delivery_radius_km')->suffix(' كم')->label('حد التوصيل العادي'),
                TextEntry::make('extra_delivery_fee_per_km')->money('SYP')->label('أجرة الكيلومتر الزائد'),
                TextEntry::make('currentSubscription.plan.name')->label('الاشتراك الحالي')->placeholder('-'),
                TextEntry::make('currentSubscription.end_date')->dateTime()->label('انتهاء الاشتراك')->placeholder('-'),
                TextEntry::make('created_at')->dateTime()->label('تاريخ الإنشاء'),
                TextEntry::make('updated_at')->dateTime()->label('آخر تحديث'),
            ]);
    }
}
