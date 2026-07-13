<?php

namespace App\Filament\Resources\Companies\Tables;

use App\Models\Order;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                $companyOrders = fn () => Order::query()
                    ->where('orders.status', '!=', 'cancelled')
                    ->whereExists(function ($subQuery): void {
                        $subQuery
                            ->selectRaw('1')
                            ->from('order_product_detail as opd')
                            ->join(
                                'product_details as pd',
                                'pd.id',
                                '=',
                                'opd.product_detail_id'
                            )
                            ->whereColumn('opd.order_id', 'orders.id')
                            ->whereColumn('pd.company_id', 'companies.id');
                    });

                return $query->addSelect([
                    'platform_orders_count' => $companyOrders()
                        ->selectRaw('COUNT(*)'),
                    'platform_profit' => $companyOrders()
                        ->selectRaw('COALESCE(SUM(commission), 0)'),
                ]);
            })
            ->columns([
                ImageColumn::make('logo')
                    ->disk('public')
                    ->label('الشعار')
                    ->circular(),

                TextColumn::make('name_company')
                    ->label('اسم الشركة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('المالك')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('platform_orders_count')
                    ->label('عدد الطلبات')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('platform_profit')
                    ->label('أرباح المنصة')
                    ->money(config('app.currency', 'SYP'))
                    ->sortable()
                    ->color('warning'),

                TextColumn::make('product_details_count')
                    ->counts('productDetails')
                    ->label('المنتجات'),

                TextColumn::make('drivers_count')
                    ->counts('drivers')
                    ->label('السائقون'),

                TextColumn::make('stores_count')
                    ->counts('stores')
                    ->label('المتاجر'),

                TextColumn::make('delivery_radius_km')
                    ->suffix(' كم')
                    ->label('حد التوصيل'),

                TextColumn::make('extra_delivery_fee_per_km')
                    ->money('SYP')
                    ->label('أجرة الكيلو الزائد')
                    ->toggleable(),

                TextColumn::make('currentSubscription.plan.name')
                    ->label('الاشتراك')
                    ->placeholder('-'),

                TextColumn::make('currentSubscription.end_date')
                    ->dateTime()
                    ->label('ينتهي في')
                    ->placeholder('-'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('تاريخ الإنشاء'),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
