<?php

namespace App\Filament\Resources\Drivers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DriverInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')->label('السائق'),
                TextEntry::make('user.email')->label('البريد الإلكتروني'),
                TextEntry::make('user.phone')->label('رقم الهاتف')->placeholder('-'),
                TextEntry::make('car.company.name_company')->label('الشركة'),
                TextEntry::make('car.plate_number')->label('رقم اللوحة'),
                TextEntry::make('status')->badge()->label('الحالة'),
                TextEntry::make('current_lat')->label('خط العرض')->placeholder('-'),
                TextEntry::make('current_lng')->label('خط الطول')->placeholder('-'),
                TextEntry::make('last_location_at')->dateTime()->label('آخر تحديث للموقع')->placeholder('-'),
                TextEntry::make('created_at')->dateTime()->label('تاريخ الإنشاء'),
                TextEntry::make('updated_at')->dateTime()->label('آخر تحديث'),
            ]);
    }
}
