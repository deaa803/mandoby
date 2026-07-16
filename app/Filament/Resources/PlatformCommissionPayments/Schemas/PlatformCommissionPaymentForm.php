<?php

namespace App\Filament\Resources\PlatformCommissionPayments\Schemas;

use App\Models\Company;
use App\Services\PlatformProfitService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PlatformCommissionPaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('company_id')
                ->relationship('company', 'name_company')
                ->default(fn (): ?int => request()->integer('company_id') ?: null)
                ->searchable()
                ->preload()
                ->required()
                ->helperText(function ($state): ?string {
                    if (! $state) {
                        return 'اختر الشركة لعرض مستحقاتها من لوحة الشركات.';
                    }

                    $company = Company::query()->find($state);

                    if (! $company) {
                        return null;
                    }

                    $summary = app(PlatformProfitService::class)->companyStatement($company, true);

                    return 'المستحق: ' . number_format((float) $summary['accrued_commission'], 2)
                        . ' — المدفوع: ' . number_format((float) $summary['confirmed_platform_payments'], 2)
                        . ' — المتبقي: ' . number_format((float) $summary['remaining_amount'], 2);
                })
                ->label('الشركة'),

            TextInput::make('amount')
                ->numeric()
                ->minValue(0.01)
                ->required()
                ->suffix('ل.س')
                ->label('المبلغ'),

            DateTimePicker::make('paid_at')
                ->default(now())
                ->seconds(false)
                ->required()
                ->label('تاريخ الدفع'),

            Select::make('payment_method')
                ->options([
                    'cash' => 'نقدي',
                    'bank_transfer' => 'تحويل بنكي',
                    'electronic_wallet' => 'محفظة إلكترونية',
                    'other' => 'طريقة أخرى',
                ])
                ->native(false)
                ->nullable()
                ->label('طريقة الدفع'),

            TextInput::make('reference_number')
                ->maxLength(255)
                ->nullable()
                ->label('رقم الإيصال أو التحويل'),

            Select::make('status')
                ->options([
                    'pending' => 'بانتظار المراجعة',
                    'confirmed' => 'مؤكد',
                    'rejected' => 'مرفوض',
                ])
                ->default('confirmed')
                ->native(false)
                ->required()
                ->label('الحالة'),

            FileUpload::make('proof_path')
                ->disk('public')
                ->directory('platform-commission-proofs')
                ->acceptedFileTypes([
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                    'application/pdf',
                ])
                ->maxSize(5120)
                ->nullable()
                ->label('إثبات الدفع'),

            Textarea::make('notes')
                ->rows(3)
                ->nullable()
                ->columnSpanFull()
                ->label('ملاحظات'),

            Textarea::make('rejection_reason')
                ->rows(3)
                ->nullable()
                ->columnSpanFull()
                ->label('سبب الرفض'),
        ]);
    }
}
