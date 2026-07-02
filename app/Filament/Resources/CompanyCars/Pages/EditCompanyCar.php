<?php

namespace App\Filament\Resources\CompanyCars\Pages;

use App\Filament\Resources\CompanyCars\CompanyCarResource;
use App\Models\CompanyCar;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditCompanyCar extends EditRecord
{
    protected static string $resource = CompanyCarResource::class;

    protected function afterSave(): void
    {
        $driver = $this->record->driver;

        if (! $driver) {
            return;
        }

        $driver->update([
            'company_id' => $this->record->company_id,
        ]);

        $driver->user?->update([
            'name' => $this->record->driver_name,
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->before(function (CompanyCar $record): void {
                    DB::transaction(function () use ($record): void {
                        $driverUser = $record->driver?->user;

                        if ($driverUser) {
                            $driverUser->delete();
                        }
                    });
                }),
        ];
    }
}
