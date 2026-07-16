<?php

namespace App\Filament\Resources\CompanyCars\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\CompanyCars\CompanyCarResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCompanyCar extends ViewRecord
{
    protected static string $resource = CompanyCarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('تعديل'),

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
