<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('order.id')->label('رقم الطلب'),
                TextEntry::make('order.store.name_store')->label('المتجر'),
                TextEntry::make('amount')->numeric(decimalPlaces: 2)->label('المبلغ'),
                TextEntry::make('paid_at')->dateTime()->label('تاريخ الدفع'),
                TextEntry::make('note')->label('الملاحظة')->placeholder('-')->columnSpanFull(),
                TextEntry::make('created_at')->dateTime()->label('تاريخ الإنشاء'),
                TextEntry::make('updated_at')->dateTime()->label('آخر تحديث'),
            ]);
    }
}
