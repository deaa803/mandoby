<?php

namespace App\Filament\Resources\ShippingOffices\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ShippingOfficeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')->label('اسم المكتب'),
                TextEntry::make('user.email')->label('البريد الإلكتروني'),
                TextEntry::make('user.phone')->label('رقم الهاتف')->placeholder('-'),
                TextEntry::make('created_at')->dateTime()->label('تاريخ الإنشاء'),
                TextEntry::make('updated_at')->dateTime()->label('آخر تحديث'),
            ]);
    }
}
