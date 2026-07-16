<?php

namespace App\Filament\Resources\PlatformCommissionPayments\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PlatformCommissionPaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('company.name_company')->label('الشركة'),
            TextEntry::make('amount')->money('SYP')->label('المبلغ'),
            TextEntry::make('paid_at')->dateTime()->label('تاريخ الدفع'),
            TextEntry::make('payment_method')
                ->formatStateUsing(fn (?string $state): string => match ($state) {
                    'cash' => 'نقدي',
                    'bank_transfer' => 'تحويل بنكي',
                    'electronic_wallet' => 'محفظة إلكترونية',
                    'other' => 'طريقة أخرى',
                    default => '-',
                })
                ->label('طريقة الدفع'),
            TextEntry::make('reference_number')->placeholder('-')->label('رقم الإيصال أو التحويل'),
            TextEntry::make('status')
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'pending' => 'بانتظار المراجعة',
                    'confirmed' => 'مؤكد',
                    'rejected' => 'مرفوض',
                    default => $state,
                })
                ->badge()
                ->label('الحالة'),
            TextEntry::make('submission_source')
                ->formatStateUsing(fn (string $state): string => $state === 'company' ? 'تطبيق الشركة' : 'لوحة الإدارة')
                ->label('مصدر التسجيل'),
            TextEntry::make('submittedBy.name')->placeholder('-')->label('سجلها'),
            TextEntry::make('reviewedBy.name')->placeholder('-')->label('راجعها'),
            TextEntry::make('reviewed_at')->dateTime()->placeholder('-')->label('تاريخ المراجعة'),
            TextEntry::make('notes')->placeholder('-')->columnSpanFull()->label('ملاحظات'),
            TextEntry::make('rejection_reason')->placeholder('-')->columnSpanFull()->label('سبب الرفض'),
            ImageEntry::make('proof_path')->disk('public')->label('إثبات الدفع'),
            TextEntry::make('created_at')->dateTime()->label('تاريخ الإنشاء'),
            TextEntry::make('updated_at')->dateTime()->label('آخر تحديث'),
        ]);
    }
}
