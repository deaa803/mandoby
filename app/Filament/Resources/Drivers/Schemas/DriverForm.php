<?php

namespace App\Filament\Resources\Drivers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class DriverForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship(
                        name: 'user',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query): Builder => $query->where('user_type', 'driver'),
                    )
                    ->searchable(['name', 'email'])
                    ->preload()
                    ->unique(table: 'drivers', column: 'user_id', ignoreRecord: true)
                    ->required()
                    ->label('حساب السائق'),

                Select::make('company_car_id')
                    ->relationship('car', 'plate_number')
                    ->searchable()
                    ->preload()
                    ->unique(table: 'drivers', column: 'company_car_id', ignoreRecord: true)
                    ->required()
                    ->label('السيارة'),

                Select::make('status')
                    ->options([
                        'available' => 'متاح',
                        'busy' => 'مشغول',
                        'offline' => 'غير متصل',
                    ])
                    ->default('available')
                    ->required()
                    ->native(false)
                    ->label('الحالة'),

                TextInput::make('current_lat')
                    ->numeric()
                    ->nullable()
                    ->label('خط العرض الحالي'),

                TextInput::make('current_lng')
                    ->numeric()
                    ->nullable()
                    ->label('خط الطول الحالي'),

                DateTimePicker::make('last_location_at')
                    ->label('آخر تحديث للموقع'),
            ]);
    }
}
