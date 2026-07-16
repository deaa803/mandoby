<?php

namespace App\Filament\Resources\CompanyCars;

use App\Filament\Resources\CompanyCars\Pages\CreateCompanyCar;
use App\Filament\Resources\CompanyCars\Pages\EditCompanyCar;
use App\Filament\Resources\CompanyCars\Pages\ListCompanyCars;
use App\Filament\Resources\CompanyCars\Pages\ViewCompanyCar;
use App\Filament\Resources\CompanyCars\Schemas\CompanyCarForm;
use App\Filament\Resources\CompanyCars\Schemas\CompanyCarInfolist;
use App\Filament\Resources\CompanyCars\Tables\CompanyCarsTable;
use App\Models\CompanyCar;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CompanyCarResource extends Resource
{
    protected static ?string $navigationLabel = 'سيارات الشركات';
    protected static ?string $model = CompanyCar::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static \UnitEnum|string|null $navigationGroup = 'إدارة المنصة';

    protected static ?int $navigationSort = 40;

    protected static ?string $recordTitleAttribute = 'plate_number';

    public static function form(Schema $schema): Schema
    {
        return CompanyCarForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CompanyCarInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CompanyCarsTable::configure($table);
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
            'index' => ListCompanyCars::route('/'),
            'create' => CreateCompanyCar::route('/create'),
            'view' => ViewCompanyCar::route('/{record}'),
            'edit' => EditCompanyCar::route('/{record}/edit'),
        ];
    }
}
