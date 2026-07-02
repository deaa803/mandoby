<?php

namespace App\Filament\Resources\Stores\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class StoreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship(
                        name: 'user',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query): Builder => $query->where('user_type', 'store'),
                    )
                    ->searchable(['name', 'email'])
                    ->preload()
                    ->required()
                    ->label('حساب المتجر'),

                TextInput::make('name_store')
                    ->required()
                    ->maxLength(255)
                    ->label('اسم المتجر'),

                TextInput::make('activity_type')
                    ->required()
                    ->maxLength(255)
                    ->label('نوع النشاط'),
            ]);
    }
}
