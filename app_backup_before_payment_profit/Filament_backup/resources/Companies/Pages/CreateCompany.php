<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use App\Models\Company;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data): Company {
            $user = User::create([
                'name' => $data['user_name'],
                'email' => $data['user_email'],
                'password' => Hash::make($data['user_password']),
                'phone' => $data['user_phone'] ?? null,
                'address' => ! empty($data['user_address']) ? $data['user_address'] : 'عنوان غير محدد',
                'latitude' => $data['user_latitude'] ?? null,
                'longitude' => $data['user_longitude'] ?? null,
                'user_type' => 'company',
            ]);

            unset(
                $data['user_name'],
                $data['user_email'],
                $data['user_password'],
                $data['user_phone'],
                $data['user_address'],
                $data['user_latitude'],
                $data['user_longitude']
            );

            $data['user_id'] = $user->id;

            return Company::create($data);
        });
    }
}
