<?php

namespace App\Filament\Resources\CompanyCars\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CompanyCarInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('company.name_company')->label('الشركة'),
                TextEntry::make('vehicle_type')->label('نوع السيارة'),
                TextEntry::make('plate_number')->label('رقم اللوحة'),
                TextEntry::make('driver_name')->label('اسم السائق'),
                TextEntry::make('driver.user.email')->label('بريد السائق')->placeholder('-'),
                TextEntry::make('driver.user.phone')->label('هاتف السائق')->placeholder('-'),
                TextEntry::make('driver.status')->badge()->label('حالة السائق')->placeholder('-'),
                TextEntry::make('created_at')->dateTime()->label('تاريخ الإنشاء'),
                TextEntry::make('updated_at')->dateTime()->label('آخر تحديث'),
            ]);
    }
}
