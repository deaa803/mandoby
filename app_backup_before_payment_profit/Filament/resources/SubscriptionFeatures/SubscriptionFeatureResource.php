<?php

namespace App\Filament\Resources\SubscriptionFeatures;

use App\Filament\Resources\SubscriptionFeatures\Pages\CreateSubscriptionFeature;
use App\Filament\Resources\SubscriptionFeatures\Pages\EditSubscriptionFeature;
use App\Filament\Resources\SubscriptionFeatures\Pages\ListSubscriptionFeatures;
use App\Filament\Resources\SubscriptionFeatures\Pages\ViewSubscriptionFeature;
use App\Models\SubscriptionFeature;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubscriptionFeatureResource extends Resource
{
    protected static ?string $model = SubscriptionFeature::class;

    protected static ?string $navigationLabel = 'ميزات الاشتراك';

    protected static ?string $modelLabel = 'ميزة اشتراك';

    protected static ?string $pluralModelLabel = 'ميزات الاشتراكات';

    protected static \UnitEnum|string|null $navigationGroup = 'الاشتراكات';

    protected static ?int $navigationSort = 30;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * الميزات الأساسية المعتمدة في النظام.
     *
     * المفتاح advanced_reports مستخدم فعلًا في Controllers الخاصة بالتقارير،
     * لذلك يجب عدم تغييره إلى reports فقط.
     */
    public static function defaultFeatures(): array
    {
        return [
            '3d_models' => [
                'name' => 'موديلات ثلاثية الأبعاد (3D)',
                'description' => 'تسمح للشركة بإنشاء وعرض موديلات ثلاثية الأبعاد لمنتجاتها.',
            ],
            'advanced_reports' => [
                'name' => 'التقارير المتقدمة',
                'description' => 'تسمح للشركة بمشاهدة تقارير المبيعات والأرباح والطلبات والأداء.',
            ],
            'advertisements' => [
                'name' => 'الإعلانات والعروض',
                'description' => 'تسمح للشركة باستخدام الإعلانات والعروض الترويجية لمنتجاتها.',
            ],
        ];
    }

    public static function featureOptions(): array
    {
        $options = [];

        foreach (self::defaultFeatures() as $key => $feature) {
            $options[$key] = $feature['name'];
        }

        return $options;
    }

    public static function featureLabel(?string $key): string
    {
        if (blank($key)) {
            return '-';
        }

        return self::featureOptions()[$key] ?? $key;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('key')
                ->options(self::featureOptions())
                ->native(false)
                ->live()
                ->afterStateUpdated(function (?string $state, Set $set): void {
                    $feature = self::defaultFeatures()[$state] ?? null;

                    if (! $feature) {
                        return;
                    }

                    $set('name', $feature['name']);
                    $set('description', $feature['description']);
                })
                ->required()
                ->unique(table: 'subscription_features', column: 'key', ignoreRecord: true)
                ->helperText('اختر: موديلات 3D أو التقارير أو الإعلانات. ويمكن إضافة الميزات الثلاث معًا من صفحة القائمة.')
                ->label('نوع الميزة'),

            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->helperText('يتم تعبئة الاسم تلقائيًا، ويمكنك تعديله إذا أردت.')
                ->label('اسم الميزة'),

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
            TextEntry::make('name')
                ->label('اسم الميزة'),

            TextEntry::make('key')
                ->formatStateUsing(fn (?string $state): string => self::featureLabel($state))
                ->badge()
                ->label('نوع الميزة'),

            TextEntry::make('is_active')
                ->formatStateUsing(fn (bool $state): string => $state ? 'مفعلة' : 'معطلة')
                ->badge()
                ->label('الحالة'),

            TextEntry::make('description')
                ->placeholder('-')
                ->columnSpanFull()
                ->label('الوصف'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('الميزة'),

                TextColumn::make('key')
                    ->formatStateUsing(fn (?string $state): string => self::featureLabel($state))
                    ->badge()
                    ->searchable()
                    ->label('النوع'),

                IconColumn::make('is_active')
                    ->boolean()
                    ->label('مفعلة'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('تاريخ الإنشاء'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('عرض')
                    ->icon('heroicon-o-eye'),

                EditAction::make()
                    ->label('تعديل')
                    ->icon('heroicon-o-pencil-square'),

                DeleteAction::make()
                    ->label('حذف')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد الحذف')
                    ->modalDescription('هل أنت متأكد من حذف هذه الميزة؟ سيتم فصلها عن الباقات المرتبطة بها.')
                    ->modalSubmitActionLabel('نعم، احذف')
                    ->modalCancelActionLabel('إلغاء')
                    ->successNotificationTitle('تم حذف الميزة بنجاح'),
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
