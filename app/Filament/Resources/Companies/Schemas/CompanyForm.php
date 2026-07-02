<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship(
                        name: 'user',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query): Builder => $query->where('user_type', 'company'),
                    )
                    ->searchable(['name', 'email'])
                    ->preload()
                    ->required()
                    ->label('حساب مالك الشركة'),

                TextInput::make('name_company')
                    ->required()
                    ->maxLength(255)
                    ->label('اسم الشركة'),

                Textarea::make('description')
                    ->rows(4)
                    ->nullable()
                    ->columnSpanFull()
                    ->label('الوصف'),

                FileUpload::make('logo')
                    ->image()
                    ->disk('public')
                    ->directory('companies')
                    ->visibility('public')
                    ->label('شعار الشركة'),
            ]);
    }
}
