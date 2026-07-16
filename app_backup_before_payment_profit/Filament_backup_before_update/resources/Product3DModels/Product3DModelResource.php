<?php

namespace App\Filament\Resources\Product3DModels;

use App\Filament\Resources\Product3DModels\Pages\CreateProduct3DModel;
use App\Filament\Resources\Product3DModels\Pages\EditProduct3DModel;
use App\Filament\Resources\Product3DModels\Pages\ListProduct3DModels;
use App\Filament\Resources\Product3DModels\Pages\ViewProduct3DModel;
use App\Filament\Resources\Product3DModels\Schemas\Product3DModelForm;
use App\Filament\Resources\Product3DModels\Schemas\Product3DModelInfolist;
use App\Filament\Resources\Product3DModels\Tables\Product3DModelsTable;
use App\Models\Product3DModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class Product3DModelResource extends Resource
{
    protected static ?string $model = Product3DModel::class;

    protected static ?string $navigationLabel = 'نماذج 3D';

    protected static ?string $modelLabel = 'مودل ثلاثي الأبعاد';

    protected static ?string $pluralModelLabel = 'الموديلات ثلاثية الأبعاد';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cube-transparent';

    protected static \UnitEnum|string|null $navigationGroup = 'المنتجات والمحتوى';

    protected static ?int $navigationSort = 60;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return Product3DModelForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return Product3DModelInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return Product3DModelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProduct3DModels::route('/'),
            'create' => CreateProduct3DModel::route('/create'),
            'view' => ViewProduct3DModel::route('/{record}'),
            'edit' => EditProduct3DModel::route('/{record}/edit'),
        ];
    }
}
