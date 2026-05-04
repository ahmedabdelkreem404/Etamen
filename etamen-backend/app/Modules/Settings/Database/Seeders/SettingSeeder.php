<?php

namespace App\Modules\Settings\Database\Seeders;

use App\Modules\Settings\Infrastructure\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'payments.currency',
                'value' => 'EGP',
                'group' => 'payments',
                'is_encrypted' => false,
                'description' => 'Default platform payment currency.',
            ],
            [
                'key' => 'payments.paymob.enabled',
                'value' => 'false',
                'group' => 'payments',
                'is_encrypted' => false,
                'description' => 'Paymob is configured by admin and inactive by default.',
            ],
            [
                'key' => 'payments.manual_vodafone_cash.enabled',
                'value' => 'false',
                'group' => 'payments',
                'is_encrypted' => false,
                'description' => 'Manual Vodafone Cash is inactive until admin configures it.',
            ],
            [
                'key' => 'payments.manual_instapay.enabled',
                'value' => 'false',
                'group' => 'payments',
                'is_encrypted' => false,
                'description' => 'Manual InstaPay is inactive until admin configures it.',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::query()->updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
