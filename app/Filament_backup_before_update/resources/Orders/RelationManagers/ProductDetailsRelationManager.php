<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductDetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'productDetails';

    protected static ?string $title = 'منتجات الطلب';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('price')
                    ->numeric()
                    ->minValue(0)
                    ->required()
                    ->label('السعر'),

                TextInput::make('quantity')
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->default(1)
                    ->required()
                    ->label('الكمية'),

                TextInput::make('discount')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required()
                    ->label('الخصم'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('المنتج')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('company.name_company')
                    ->label('الشركة')
                    ->searchable(),

                TextColumn::make('price')
                    ->numeric(decimalPlaces: 2)
                    ->label('سعر الطلب'),

                TextColumn::make('quantity')
                    ->numeric()
                    ->label('الكمية'),

                TextColumn::make('discount')
                    ->numeric(decimalPlaces: 2)
                    ->label('الخصم'),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('إضافة منتج')
                    ->preloadRecordSelect()
                    ->schema(fn (AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->label('المنتج'),
                        TextInput::make('price')
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            ->label('السعر'),
                        TextInput::make('quantity')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->default(1)
                            ->required()
                            ->label('الكمية'),
                        TextInput::make('discount')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required()
                            ->label('الخصم'),
                    ])
                    ->after(fn () => $this->recalculateOrder()),
            ])
            ->recordActions([
                EditAction::make()
                    ->after(fn () => $this->recalculateOrder()),
                DetachAction::make()
                    ->after(fn () => $this->recalculateOrder()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->after(fn () => $this->recalculateOrder()),
                ]),
            ]);
    }

    private function recalculateOrder(): void
    {
        $order = $this->getOwnerRecord()->fresh();

        if (! $order) {
            return;
        }

        $totalPrice = $order->productDetails()
            ->get()
            ->sum(function ($productDetail): float {
                $price = (float) $productDetail->pivot->price;
                $quantity = (int) $productDetail->pivot->quantity;
                $discount = (float) $productDetail->pivot->discount;

                return max(0, ($price * $quantity) - $discount);
            });

        $paidAmount = (float) $order->payments()->sum('amount');

        $order->update([
            'total_price' => $totalPrice,
            'paid_amount' => $paidAmount,
            'remaining_amount' => max(0, $totalPrice - $paidAmount),
        ]);
    }
}
