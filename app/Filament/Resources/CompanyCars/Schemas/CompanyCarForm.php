<?php

namespace App\Filament\Resources\CompanyCars\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CompanyCarForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->relationship('company', 'name_company')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('الشركة'),

                TextInput::make('vehicle_type')
                    ->required()
                    ->maxLength(255)
                    ->label('نوع السيارة'),

                TextInput::make('plate_number')
                    ->required()
                    ->maxLength(255)
                    ->label('رقم اللوحة'),

                TextInput::make('driver_name')
                    ->required()
                    ->maxLength(255)
                    ->label('اسم السائق'),

                TextInput::make('driver_email')
                    ->email()
                    ->unique('users', 'email')
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->label('بريد حساب السائق'),

                TextInput::make('driver_password')
                    ->password()
                    ->revealable()
                    ->minLength(8)
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->label('كلمة مرور السائق'),

                TextInput::make('driver_phone')
                    ->tel()
                    ->maxLength(30)
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->label('رقم هاتف السائق'),
            ]);
    }
}
