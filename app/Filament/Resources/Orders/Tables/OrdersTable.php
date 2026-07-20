<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Driver;
use App\Models\Order;
use App\Services\DriverAssignmentService;
use App\Services\SmartDispatchService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('رقم الطلب')->sortable(),
                TextColumn::make('store.name_store')->label('المتجر')->searchable()->sortable(),
                TextColumn::make('total_price')->numeric(decimalPlaces: 2)->label('الإجمالي')->sortable(),
                TextColumn::make('total_weight_kg')
                    ->numeric(decimalPlaces: 3)
                    ->suffix(' كغ')
                    ->label('وزن الطلب')
                    ->sortable(),
                TextColumn::make('required_load_kg')
                    ->numeric(decimalPlaces: 3)
                    ->suffix(' كغ')
                    ->label('الحمولة المطلوبة')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('paid_amount')->numeric(decimalPlaces: 2)->label('المدفوع')->sortable(),
                TextColumn::make('remaining_amount')->numeric(decimalPlaces: 2)->label('المتبقي')->sortable(),
                TextColumn::make('date')->date()->label('التاريخ')->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'قيد الانتظار',
                        'preparing' => 'قيد التجهيز',
                        'delivering' => 'قيد التوصيل',
                        'delivered' => 'مكتمل',
                        'cancelled' => 'ملغي',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'preparing' => 'info',
                        'delivering' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->label('الحالة'),
                TextColumn::make('driver.user.name')->label('السائق')->searchable()->placeholder('-'),
                TextColumn::make('driver.car.vehicle_type')->label('السيارة')->placeholder('-'),
                TextColumn::make('driver_assignment_method')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'smart' => 'اقتراح ذكي',
                        'manual' => 'اختيار يدوي',
                        default => '-',
                    })
                    ->color(fn (?string $state): string => $state === 'smart' ? 'success' : 'gray')
                    ->label('طريقة التعيين'),
                TextColumn::make('commission')->numeric(decimalPlaces: 2)->label('عمولة المنصة من الدفعات')->sortable(),
                TextColumn::make('created_at')->dateTime()->label('تاريخ الإنشاء')->toggleable(isToggledHiddenByDefault: true)->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'preparing' => 'قيد التجهيز',
                        'delivering' => 'قيد التوصيل',
                        'delivered' => 'مكتمل',
                        'cancelled' => 'ملغي',
                    ])
                    ->label('الحالة'),
            ])
            ->recordActions([
                Action::make('smartDispatch')
                    ->label('اقتراح سائق ذكي')
                    ->icon('heroicon-o-sparkles')
                    ->color('success')
                    ->visible(fn (Order $record): bool => ! in_array($record->status, ['delivered', 'cancelled'], true))
                    ->modalHeading('اقتراح السائق الأنسب')
                    ->modalDescription(fn (Order $record): string =>
                        'وزن الطلب: ' . number_format((float) $record->total_weight_kg, 3)
                        . ' كغ — الحمولة المطلوبة مع هامش الأمان: '
                        . number_format((float) $record->required_load_kg, 3) . ' كغ')
                    ->schema([
                        Select::make('driver_id')
                            ->options(fn (Order $record): array => self::driverOptions($record, true))
                            ->default(fn (Order $record): ?int => self::firstRecommendedDriverId($record))
                            ->searchable()
                            ->required()
                            ->label('السائق المقترح مع سيارته')
                            ->helperText('السائقون مرتبون حسب حمولة السيارة، القرب، وضغط العمل.'),
                    ])
                    ->action(fn (Order $record, array $data) => self::assign($record, (int) $data['driver_id'], 'smart'))
                    ->modalSubmitActionLabel('اعتماد الاقتراح'),

                Action::make('manualDispatch')
                    ->label('اختيار سائق يدويًا')
                    ->icon('heroicon-o-user-plus')
                    ->color('info')
                    ->visible(fn (Order $record): bool => ! in_array($record->status, ['delivered', 'cancelled'], true))
                    ->modalHeading('اختيار السائق يدويًا')
                    ->schema([
                        Select::make('driver_id')
                            ->options(fn (Order $record): array => self::driverOptions($record, false))
                            ->searchable()
                            ->required()
                            ->label('سائق من نفس الشركة بسيارة مناسبة'),
                    ])
                    ->action(fn (Order $record, array $data) => self::assign($record, (int) $data['driver_id'], 'manual'))
                    ->modalSubmitActionLabel('تأكيد التعيين'),

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
                    ->modalDescription('هل أنت متأكد من حذف هذا السجل؟ لا يمكن التراجع عن العملية بعد تنفيذها.')
                    ->modalSubmitActionLabel('نعم، احذف')
                    ->modalCancelActionLabel('إلغاء')
                    ->successNotificationTitle('تم حذف السجل بنجاح'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    private static function driverOptions(Order $order, bool $includeScore): array
    {
        try {
            $result = app(SmartDispatchService::class)->recommendations($order);

            return collect($result['recommendations'])
                ->mapWithKeys(function (array $driver) use ($includeScore): array {
                    $label = ($driver['driver_name'] ?? 'سائق')
                        . ' — ' . ($driver['vehicle_type'] ?? 'سيارة')
                        . ' — حمولة ' . number_format((float) $driver['max_load_kg'], 0) . ' كغ'
                        . ' — استغلال ' . number_format((float) $driver['capacity_utilization_percent'], 1) . '٪';

                    if ($includeScore) {
                        $label .= ' — تقييم ' . number_format((float) $driver['score'], 1) . '٪';
                    }

                    return [(int) $driver['driver_id'] => $label];
                })
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    private static function firstRecommendedDriverId(Order $order): ?int
    {
        $options = self::driverOptions($order, true);
        $first = array_key_first($options);

        return $first === null ? null : (int) $first;
    }

    private static function assign(Order $order, int $driverId, string $method): void
    {
        try {
            $driver = Driver::query()->findOrFail($driverId);
            app(DriverAssignmentService::class)->assign($order, $driver, $method);

            Notification::make()
                ->title($method === 'smart' ? 'تم اعتماد السائق المقترح' : 'تم تعيين السائق يدويًا')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('تعذر تعيين السائق')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
