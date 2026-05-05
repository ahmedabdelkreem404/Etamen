<?php

namespace Database\Seeders;

use App\Modules\AI\Database\Seeders\AiProviderConfigSeeder;
use App\Modules\Identity\Database\Seeders\RoleSeeder;
use App\Modules\Identity\Database\Seeders\SuperAdminSeeder;
use App\Modules\Payments\Database\Seeders\PaymentMethodSeeder;
use App\Modules\Settings\Database\Seeders\SettingSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SettingSeeder::class,
            PaymentMethodSeeder::class,
            AiProviderConfigSeeder::class,
            SuperAdminSeeder::class,
        ]);
    }
}
