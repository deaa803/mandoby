<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('اسم المنتج'),
                Textarea::make('description')
                    ->rows(4)
                    ->nullable()
                    ->columnSpanFull()
                    ->label('الوصف'),
            ]);
    }
}
