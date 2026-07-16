<?php

namespace App\Filament\Resources\CompanyCars\Pages;

use App\Filament\Resources\CompanyCars\CompanyCarResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCompanyCar extends EditRecord
{
    protected static string $resource = CompanyCarResource::class;

    protected ?string $driverName = null;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['driver_name'] = $this->record->driver?->user?->name;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->driverName = $data['driver_name'] ?? null;
        unset($data['driver_name']);

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->driverName !== null && $this->record->driver?->user) {
            $this->record->driver->user->update([
                'name' => $this->driverName,
            ]);
        }
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
}
