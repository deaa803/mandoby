<?php

namespace App\Filament\Resources\SubscriptionPlans;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\SubscriptionPlans\Pages\CreateSubscriptionPlan;
use App\Filament\Resources\SubscriptionPlans\Pages\EditSubscriptionPlan;
use App\Filament\Resources\SubscriptionPlans\Pages\ListSubscriptionPlans;
use App\Filament\Resources\SubscriptionPlans\Pages\ViewSubscriptionPlan;
use App\Models\SubscriptionPlan;
use BackedEnum;
use Filament\Forms\Components\CheckboxList;
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

class SubscriptionPlanResource extends Resource
{
    protected static ?string $model = SubscriptionPlan::class;

    protected static ?string $navigationLabel = 'باقات الاشتراك';

    protected static ?string $modelLabel = 'باقة اشتراك';

    protected static ?string $pluralModelLabel = 'باقات الاشتراك';

    protected static \UnitEnum|string|null $navigationGroup = 'الاشتراكات';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->label('اسم الباقة'),

            TextInput::make('price')
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->required()
                ->label('السعر'),

            TextInput::make('duration_days')
                ->numeric()
                ->integer()
                ->minValue(1)
                ->default(30)
                ->required()
                ->label('المدة بالأيام'),

            Toggle::make('is_active')
                ->default(true)
                ->label('مفعلة'),

            CheckboxList::make('features')
                ->relationship(titleAttribute: 'name')
                ->columns(2)
                ->label('ميزات الباقة'),

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
            TextEntry::make('name')->label('اسم الباقة'),
            TextEntry::make('price')->numeric(decimalPlaces: 2)->label('السعر'),
            TextEntry::make('duration_days')->numeric()->label('المدة بالأيام'),
            TextEntry::make('features.name')->badge()->label('الميزات'),
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
                TextColumn::make('name')->searchable()->sortable()->label('اسم الباقة'),
                TextColumn::make('price')->numeric(decimalPlaces: 2)->sortable()->label('السعر'),
                TextColumn::make('duration_days')->sortable()->label('الأيام'),
                TextColumn::make('features.name')->badge()->label('الميزات'),
                IconColumn::make('is_active')->boolean()->label('مفعلة'),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)->label('تاريخ الإنشاء'),
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
                DeleteAction::make()
                                    ->label('حذف')
                                    ->icon('heroicon-o-trash')
                                    ->color('danger')
                                    ->modalHeading('تأكيد حذف السجل')
                                    ->modalDescription('هل أنت متأكد من حذف هذا السجل؟ قد يؤدي الحذف إلى إزالة البيانات المرتبطة به حسب علاقات قاعدة البيانات، ولا يمكن التراجع عن العملية.')
                                    ->modalSubmitActionLabel('نعم، احذف')
                                    ->modalCancelActionLabel('إلغاء')
                                    ->successNotificationTitle('تم حذف السجل بنجاح'),
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
            'index' => ListSubscriptionPlans::route('/'),
            'create' => CreateSubscriptionPlan::route('/create'),
            'view' => ViewSubscriptionPlan::route('/{record}'),
            'edit' => EditSubscriptionPlan::route('/{record}/edit'),
        ];
    }
}
