<?php

namespace App\Modules\Identity\Database\Seeders;

use App\Models\User;
use App\Modules\Identity\Domain\Enums\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $name = env('SUPER_ADMIN_NAME');
        $email = env('SUPER_ADMIN_EMAIL');
        $password = env('SUPER_ADMIN_PASSWORD');

        if ((! $name || ! $email || ! $password) && ! app()->environment('local')) {
            return;
        }

        $name = $name ?: 'Etamen Local Super Admin';
        $email = $email ?: 'admin@etamen.local';
        $password = $password ?: 'ChangeMeLocalOnly!2026';

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
            ],
        );

        $user->assignRole(UserRole::SuperAdmin->value);
    }
}
