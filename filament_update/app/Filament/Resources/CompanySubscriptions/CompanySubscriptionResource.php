<?php

namespace App\Filament\Resources\CompanySubscriptions;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\CompanySubscriptions\Pages\CreateCompanySubscription;
use App\Filament\Resources\CompanySubscriptions\Pages\EditCompanySubscription;
use App\Filament\Resources\CompanySubscriptions\Pages\ListCompanySubscriptions;
use App\Filament\Resources\CompanySubscriptions\Pages\ViewCompanySubscription;
use App\Models\CompanySubscription;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CompanySubscriptionResource extends Resource
{
    protected static ?string $model = CompanySubscription::class;

    protected static ?string $navigationLabel = 'اشتراكات الشركات';

    protected static ?string $modelLabel = 'اشتراك شركة';

    protected static ?string $pluralModelLabel = 'اشتراكات الشركات';

    protected static \UnitEnum|string|null $navigationGroup = 'الاشتراكات';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('company_id')
                ->relationship('company', 'name_company')
                ->searchable()
                ->preload()
                ->required()
                ->label('الشركة'),

            Select::make('subscription_plan_id')
                ->relationship('plan', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->label('الباقة'),

            DateTimePicker::make('start_date')
                ->default(now())
                ->seconds(false)
                ->nullable()
                ->label('تاريخ البداية'),

            DateTimePicker::make('end_date')
                ->seconds(false)
                ->nullable()
                ->label('تاريخ النهاية'),

            Select::make('status')
                ->options([
                    'active' => 'فعال',
                    'expiring' => 'قارب على الانتهاء',
                    'expired' => 'منتهي',
                    'cancelled' => 'ملغي',
                ])
                ->default('active')
                ->required()
                ->native(false)
                ->label('الحالة'),

            Textarea::make('notes')
                ->rows(3)
                ->nullable()
                ->columnSpanFull()
                ->label('ملاحظات'),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('company.name_company')->label('الشركة'),
            TextEntry::make('plan.name')->label('الباقة'),
            TextEntry::make('plan.features.name')->badge()->label('الميزات'),
            TextEntry::make('start_date')->dateTime()->placeholder('-')->label('تاريخ البداية'),
            TextEntry::make('end_date')->dateTime()->placeholder('-')->label('تاريخ النهاية'),
            TextEntry::make('days_remaining')->placeholder('-')->label('الأيام المتبقية'),
            TextEntry::make('status')
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'active' => 'فعال',
                    'expiring' => 'قارب على الانتهاء',
                    'expired' => 'منتهي',
                    'cancelled' => 'ملغي',
                    default => $state,
                })
                ->badge()
                ->label('الحالة'),
            TextEntry::make('notes')->placeholder('-')->columnSpanFull()->label('ملاحظات'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name_company')->searchable()->sortable()->label('الشركة'),
                TextColumn::make('plan.name')->searchable()->sortable()->label('الباقة'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'فعال',
                        'expiring' => 'قارب على الانتهاء',
                        'expired' => 'منتهي',
                        'cancelled' => 'ملغي',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'expiring' => 'warning',
                        'expired' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    })
                    ->label('الحالة'),
                TextColumn::make('start_date')->dateTime()->sortable()->label('البداية'),
                TextColumn::make('end_date')->dateTime()->sortable()->label('النهاية'),
                TextColumn::make('days_remaining')->label('المتبقي'),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)->label('تاريخ الإنشاء'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'فعال',
                        'expiring' => 'قارب على الانتهاء',
                        'expired' => 'منتهي',
                        'cancelled' => 'ملغي',
                    ])
                    ->label('الحالة'),
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
            'index' => ListCompanySubscriptions::route('/'),
            'create' => CreateCompanySubscription::route('/create'),
            'view' => ViewCompanySubscription::route('/{record}'),
            'edit' => EditCompanySubscription::route('/{record}/edit'),
        ];
    }
}
