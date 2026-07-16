<?php

namespace App\Filament\Resources\PlatformCommissionPayments;

use App\Filament\Resources\PlatformCommissionPayments\Pages\CreatePlatformCommissionPayment;
use App\Filament\Resources\PlatformCommissionPayments\Pages\EditPlatformCommissionPayment;
use App\Filament\Resources\PlatformCommissionPayments\Pages\ListPlatformCommissionPayments;
use App\Filament\Resources\PlatformCommissionPayments\Pages\ViewPlatformCommissionPayment;
use App\Filament\Resources\PlatformCommissionPayments\Schemas\PlatformCommissionPaymentForm;
use App\Filament\Resources\PlatformCommissionPayments\Schemas\PlatformCommissionPaymentInfolist;
use App\Filament\Resources\PlatformCommissionPayments\Tables\PlatformCommissionPaymentsTable;
use App\Models\PlatformCommissionPayment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PlatformCommissionPaymentResource extends Resource
{
    protected static ?string $model = PlatformCommissionPayment::class;

    protected static ?string $navigationLabel = 'دفعات الشركات للمنصة';

    protected static ?string $modelLabel = 'دفعة شركة للمنصة';

    protected static ?string $pluralModelLabel = 'دفعات الشركات للمنصة';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-receipt-percent';

    protected static \UnitEnum|string|null $navigationGroup = 'الطلبات والمالية';

    protected static ?int $navigationSort = 30;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return PlatformCommissionPaymentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PlatformCommissionPaymentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PlatformCommissionPaymentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlatformCommissionPayments::route('/'),
            'create' => CreatePlatformCommissionPayment::route('/create'),
            'view' => ViewPlatformCommissionPayment::route('/{record}'),
            'edit' => EditPlatformCommissionPayment::route('/{record}/edit'),
        ];
    }
}
