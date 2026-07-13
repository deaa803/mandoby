<?php

namespace App\Filament\Resources\ProductDetails;

use App\Filament\Resources\ProductDetails\Pages\CreateProductDetail;
use App\Filament\Resources\ProductDetails\Pages\EditProductDetail;
use App\Filament\Resources\ProductDetails\Pages\ListProductDetails;
use App\Filament\Resources\ProductDetails\Pages\ViewProductDetail;
use App\Filament\Resources\ProductDetails\RelationManagers\FeaturesRelationManager;
use App\Filament\Resources\ProductDetails\Schemas\ProductDetailForm;
use App\Filament\Resources\ProductDetails\Schemas\ProductDetailInfolist;
use App\Filament\Resources\ProductDetails\Tables\ProductDetailsTable;
use App\Models\ProductDetail;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductDetailResource extends Resource
{
    protected static ?string $model = ProductDetail::class;


    protected static ?string $navigationLabel = 'تفاصيل المنتجات';


    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return ProductDetailForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProductDetailInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductDetailsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            FeaturesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductDetails::route('/'),
            'create' => CreateProductDetail::route('/create'),
            'view' => ViewProductDetail::route('/{record}'),
            'edit' => EditProductDetail::route('/{record}/edit'),
        ];
    }
}
