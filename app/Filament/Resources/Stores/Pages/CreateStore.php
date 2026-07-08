<?php

namespace App\Filament\Resources\Stores\Pages;

use App\Filament\Resources\Stores\StoreResource;
use App\Models\Store;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateStore extends CreateRecord
{
    protected static string $resource = StoreResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data): Store {
            $user = User::create([
                'name' => $data['user_name'],
                'email' => $data['user_email'],
                'password' => Hash::make($data['user_password']),
                'phone' => $data['user_phone'] ?? null,
                'address' => ! empty($data['user_address']) ? $data['user_address'] : 'عنوان غير محدد',
                'latitude' => $data['user_latitude'] ?? null,
                'longitude' => $data['user_longitude'] ?? null,
                'user_type' => 'store',
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

            return Store::create($data);
        });
    }
}
