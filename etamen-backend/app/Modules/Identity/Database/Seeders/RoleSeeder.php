<?php

namespace App\Modules\Identity\Database\Seeders;

use App\Modules\Identity\Domain\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (UserRole::values() as $role) {
            Role::findOrCreate($role);
        }
    }
}
