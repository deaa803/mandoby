<?php

namespace App\Filament\Resources\SubscriptionFeatures;

use App\Filament\Resources\SubscriptionFeatures\Pages\CreateSubscriptionFeature;
use App\Filament\Resources\SubscriptionFeatures\Pages\EditSubscriptionFeature;
use App\Filament\Resources\SubscriptionFeatures\Pages\ListSubscriptionFeatures;
use App\Filament\Resources\SubscriptionFeatures\Pages\ViewSubscriptionFeature;
use App\Models\SubscriptionFeature;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubscriptionFeatureResource extends Resource
{
    protected static ?string $model = SubscriptionFeature::class;

    protected static ?string $navigationLabel = 'ميزات الاشتراكات';

    protected static ?string $modelLabel = 'ميزة اشتراك';

    protected static ?string $pluralModelLabel = 'ميزات الاشتراكات';

    protected static \UnitEnum|string|null $navigationGroup = 'الاشتراكات';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->label('اسم الميزة'),

            TextInput::make('key')
                ->required()
                ->maxLength(255)
                ->unique(table: 'subscription_features', column: 'key', ignoreRecord: true)
                ->helperText('مثال: 3d_models أو advanced_reports')
                ->label('المفتاح'),

            Toggle::make('is_active')
                ->default(true)
                ->label('مفعلة'),

            Textarea::make('description')
                ->rows(3)
                ->nullable()
                ->columnSpanFull()
                ->label('الوصف'),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('name')->label('اسم الميزة'),
            TextEntry::make('key')->copyable()->label('المفتاح'),
            TextEntry::make('is_active')
                ->formatStateUsing(fn (bool $state): string => $state ? 'مفعلة' : 'معطلة')
                ->badge()
                ->label('الحالة'),
            TextEntry::make('description')->placeholder('-')->columnSpanFull()->label('الوصف'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->label('الميزة'),
                TextColumn::make('key')->searchable()->copyable()->label('المفتاح'),
                IconColumn::make('is_active')->boolean()->label('مفعلة'),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)->label('تاريخ الإنشاء'),
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubscriptionFeatures::route('/'),
            'create' => CreateSubscriptionFeature::route('/create'),
            'view' => ViewSubscriptionFeature::route('/{record}'),
            'edit' => EditSubscriptionFeature::route('/{record}/edit'),
        ];
    }
}
