<?php

namespace App\Modules\Payments\Database\Seeders;

use App\Modules\Payments\Domain\Enums\PaymentMethodType;
use App\Modules\Payments\Infrastructure\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'type' => PaymentMethodType::Paymob,
                'name_ar' => 'Paymob',
                'name_en' => 'Paymob',
                'instructions_ar' => null,
                'instructions_en' => null,
                'sort_order' => 1,
            ],
            [
                'type' => PaymentMethodType::ManualVodafoneCash,
                'name_ar' => 'فودافون كاش',
                'name_en' => 'Vodafone Cash',
                'instructions_ar' => 'سيتم ضبط رقم فودافون كاش وتعليمات التحويل من لوحة الإدارة.',
                'instructions_en' => 'Vodafone Cash number and transfer instructions will be configured by admin.',
                'sort_order' => 2,
            ],
            [
                'type' => PaymentMethodType::ManualInstapay,
                'name_ar' => 'إنستا باي',
                'name_en' => 'InstaPay',
                'instructions_ar' => 'سيتم ضبط حساب إنستا باي وتعليمات التحويل من لوحة الإدارة.',
                'instructions_en' => 'InstaPay handle/account and transfer instructions will be configured by admin.',
                'sort_order' => 3,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::query()->updateOrCreate(
                ['type' => $method['type']],
                [
                    ...$method,
                    'is_active' => false,
                    'config' => null,
                ],
            );
        }
    }
}
