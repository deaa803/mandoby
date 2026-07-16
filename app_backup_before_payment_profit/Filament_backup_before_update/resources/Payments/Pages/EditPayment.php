<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    private ?int $previousOrderId = null;

    protected function beforeSave(): void
    {
        $this->previousOrderId = $this->record->order_id;
    }

    protected function afterSave(): void
    {
        if ($this->previousOrderId) {
            self::syncOrder($this->previousOrderId);
        }

        self::syncOrder($this->record->order_id);
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('عرض'),

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
        ];
    }

    private static function syncOrder(int $orderId): void
    {
        $order = Order::find($orderId);

        if (! $order) {
            return;
        }

        $paidAmount = (float) $order->payments()->sum('amount');
        $totalPrice = (float) $order->total_price;

        $order->update([
            'paid_amount' => $paidAmount,
            'remaining_amount' => max(0, $totalPrice - $paidAmount),
        ]);
    }
}
