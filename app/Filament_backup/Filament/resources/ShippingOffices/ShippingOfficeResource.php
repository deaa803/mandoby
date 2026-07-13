<?php

namespace App\Filament\Resources\ShippingOffices;

use App\Filament\Resources\ShippingOffices\Pages\CreateShippingOffice;
use App\Filament\Resources\ShippingOffices\Pages\EditShippingOffice;
use App\Filament\Resources\ShippingOffices\Pages\ListShippingOffices;
use App\Filament\Resources\ShippingOffices\Pages\ViewShippingOffice;
use App\Filament\Resources\ShippingOffices\Schemas\ShippingOfficeForm;
use App\Filament\Resources\ShippingOffices\Schemas\ShippingOfficeInfolist;
use App\Filament\Resources\ShippingOffices\Tables\ShippingOfficesTable;
use App\Models\ShippingOffice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ShippingOfficeResource extends Resource
{
    protected static ?string $navigationLabel = 'مكاتب الشحن';

    protected static ?string $modelLabel = 'مكتب شحن';

    protected static ?string $pluralModelLabel = 'مكاتب الشحن';

    protected static \UnitEnum|string|null $navigationGroup = 'إدارة المنصة';

    protected static ?int $navigationSort = 5;
    protected static ?string $model = ShippingOffice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return ShippingOfficeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ShippingOfficeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShippingOfficesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShippingOffices::route('/'),
            'create' => CreateShippingOffice::route('/create'),
            'view' => ViewShippingOffice::route('/{record}'),
            'edit' => EditShippingOffice::route('/{record}/edit'),
        ];
    }
}
