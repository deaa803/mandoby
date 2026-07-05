<?php

namespace App\Filament\Resources\CompanyCars\Pages;

use App\Filament\Resources\CompanyCars\CompanyCarResource;
use App\Models\CompanyCar;
use App\Models\Driver;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateCompanyCar extends CreateRecord
{
    protected static string $resource = CompanyCarResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data): CompanyCar {
            $driverName = $data['driver_name'];
            $driverEmail = $data['driver_email'];
            $driverPassword = $data['driver_password'];
            $driverPhone = $data['driver_phone'] ?? null;

            unset(
                $data['driver_name'],
                $data['driver_email'],
                $data['driver_password'],
                $data['driver_phone']
            );

            $car = CompanyCar::create($data);

            $user = User::create([
                'name' => $driverName,
                'email' => $driverEmail,
                'password' => $driverPassword,
                'phone' => $driverPhone,
                'user_type' => 'driver',
            ]);

            Driver::create([
                'user_id' => $user->id,
                'company_car_id' => $car->id,
                'status' => 'available',
            ]);

            return $car;
        });
    }
}
