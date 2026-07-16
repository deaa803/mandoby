<?php

namespace App\Filament\Resources\Advertisements\Pages;

use App\Filament\Resources\Advertisements\AdvertisementResource;
use App\Models\ProductDetail;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditAdvertisement extends EditRecord
{
    protected static string $resource = AdvertisementResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->validateSelectedProductCompany($data);

        return $data;
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

    private function validateSelectedProductCompany(array $data): void
    {
        $productDetailId = $data['product_detail_id'] ?? null;
        $companyId = $data['company_id'] ?? null;

        if (blank($productDetailId)) {
            return;
        }

        $belongsToCompany = ProductDetail::query()
            ->whereKey($productDetailId)
            ->where('company_id', $companyId)
            ->exists();

        if (! $belongsToCompany) {
            throw ValidationException::withMessages([
                'data.product_detail_id' => 'المنتج المختار لا يتبع للشركة المحددة. اختر منتجًا من منتجات الشركة نفسها.',
            ]);
        }
    }
}
