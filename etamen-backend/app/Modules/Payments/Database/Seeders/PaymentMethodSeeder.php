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
                'type' => PaymentMethodType::ManualVodafoneCash,
                'name_ar' => 'فودافون كاش',
                'name_en' => 'Vodafone Cash',
                'instructions_ar' => 'تعليمات تجريبية للاستيجنج فقط: حوّل إلى رقم الاختبار المعتمد ثم ارفع صورة الإيصال. لا تستخدم هذه البيانات كتحصيل إنتاجي.',
                'instructions_en' => 'Staging-only instructions: transfer to the approved test number, then upload a proof image. Do not use these details for production collection.',
                'sort_order' => 10,
                'is_active' => true,
            ],
            [
                'type' => PaymentMethodType::ManualInstapay,
                'name_ar' => 'إنستا باي',
                'name_en' => 'InstaPay',
                'instructions_ar' => 'تعليمات تجريبية للاستيجنج فقط: حوّل إلى حساب الاختبار المعتمد ثم ارفع صورة الإيصال. لا تستخدم هذه البيانات كتحصيل إنتاجي.',
                'instructions_en' => 'Staging-only instructions: transfer to the approved test account, then upload a proof image. Do not use these details for production collection.',
                'sort_order' => 20,
                'is_active' => true,
            ],
            [
                'type' => PaymentMethodType::Paymob,
                'name_ar' => 'Paymob',
                'name_en' => 'Paymob',
                'instructions_ar' => null,
                'instructions_en' => null,
                'sort_order' => 30,
                'is_active' => false,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::query()->updateOrCreate(
                ['type' => $method['type']],
                [
                    ...$method,
                    'config' => null,
                ],
            );
        }
    }
}
