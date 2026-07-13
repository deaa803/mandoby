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
                    ->unique(table: 'stores', column: 'user_id', ignoreRecord: true)
                    ->required(fn (string $operation): bool => $operation === 'edit')
                    ->visible(fn (string $operation): bool => $operation === 'edit')
                    ->label('حساب المتجر'),

                TextInput::make('user_name')
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->maxLength(255)
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->label('اسم صاحب المتجر'),

                TextInput::make('user_email')
                    ->email()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->maxLength(255)
                    ->unique(table: 'users', column: 'email')
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->label('بريد صاحب المتجر'),

                TextInput::make('user_password')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->minLength(8)
                    ->same('user_password_confirmation')
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->label('كلمة المرور'),

                TextInput::make('user_password_confirmation')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(false)
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->label('تأكيد كلمة المرور'),

                TextInput::make('user_phone')
                    ->tel()
                    ->maxLength(30)
                    ->nullable()
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->label('رقم هاتف صاحب المتجر'),

                TextInput::make('user_address')
                    ->maxLength(255)
                    ->nullable()
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->label('عنوان صاحب المتجر'),

                TextInput::make('user_latitude')
                    ->numeric()
                    ->nullable()
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->label('خط العرض'),

                TextInput::make('user_longitude')
                    ->numeric()
                    ->nullable()
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->label('خط الطول'),

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
