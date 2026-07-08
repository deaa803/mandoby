<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
                        modifyQueryUsing: fn (Builder $query): Builder =>
                        $query->where('user_type', 'company'),
                    )
                    ->searchable(['name', 'email'])
                    ->preload()
                    ->unique(table: 'companies', column: 'user_id', ignoreRecord: true)
                    ->required(fn (string $operation): bool => $operation === 'edit')
                    ->visible(fn (string $operation): bool => $operation === 'edit')
                    ->label('حساب مالك الشركة'),

                TextInput::make('user_name')
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->maxLength(255)
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->label('اسم مالك الشركة'),

                TextInput::make('user_email')
                    ->email()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->maxLength(255)
                    ->unique(table: 'users', column: 'email')
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->label('بريد مالك الشركة'),

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
                    ->label('رقم هاتف مالك الشركة'),

                TextInput::make('user_address')
                    ->maxLength(255)
                    ->nullable()
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->label('عنوان مالك الشركة'),

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

                Toggle::make('has_3d_access')
                    ->label('تفعيل خدمة إنشاء موديلات 3D')
                    ->helperText('تُفعّل هذه الخدمة فقط للشركات المشتركة والدافعة')
                    ->default(false)
                    ->live(),

                DateTimePicker::make('model_3d_expires_at')
                    ->label('تاريخ انتهاء اشتراك خدمة 3D')
                    ->helperText('اترك الحقل فارغاً إذا كان الاشتراك بدون تاريخ انتهاء')
                    ->seconds(false)
                    ->nullable()
                    ->visible(
                        fn ($get): bool =>
                        (bool) $get('has_3d_access')
                    ),
            ]);
    }
}
