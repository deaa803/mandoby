<?php

namespace App\Filament\Resources\Stores\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StoreInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name_store')->label('اسم المتجر'),
                TextEntry::make('activity_type')->label('نوع النشاط'),
                TextEntry::make('user.name')->label('صاحب المتجر'),
                TextEntry::make('user.email')->label('البريد الإلكتروني'),
                TextEntry::make('user.phone')->label('رقم الهاتف')->placeholder('-'),
                TextEntry::make('created_at')->dateTime()->label('تاريخ الإنشاء'),
                TextEntry::make('updated_at')->dateTime()->label('آخر تحديث'),
            ]);
    }
}
