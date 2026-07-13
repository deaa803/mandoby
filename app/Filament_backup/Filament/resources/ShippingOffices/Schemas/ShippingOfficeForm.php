<?php

namespace App\Filament\Resources\ShippingOffices\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class ShippingOfficeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable(['name', 'email'])
                    ->preload()
                    ->required()
                    ->label('حساب مكتب الشحن'),
            ]);
    }
}
