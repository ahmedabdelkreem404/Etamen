<?php

namespace App\Modules\Radiology\Database\Seeders;

use App\Modules\Radiology\Infrastructure\Models\RadiologyScanCategory;
use Illuminate\Database\Seeder;

class RadiologyScanCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->categories() as $index => $category) {
            RadiologyScanCategory::query()->updateOrCreate(
                ['code' => $category['code']],
                [
                    'name_ar' => $category['name_ar'],
                    'name_en' => $category['name_en'],
                    'description_ar' => $category['description_ar'] ?? null,
                    'description_en' => $category['description_en'] ?? null,
                    'is_active' => true,
                    'sort_order' => ($index + 1) * 10,
                ],
            );
        }
    }

    private function categories(): array
    {
        return [
            ['code' => 'x_ray', 'name_ar' => 'أشعة عادية', 'name_en' => 'X-Ray'],
            ['code' => 'ultrasound', 'name_ar' => 'موجات فوق صوتية', 'name_en' => 'Ultrasound'],
            ['code' => 'ct_scan', 'name_ar' => 'أشعة مقطعية', 'name_en' => 'CT Scan'],
            ['code' => 'mri', 'name_ar' => 'رنين مغناطيسي', 'name_en' => 'MRI'],
            ['code' => 'mammography', 'name_ar' => 'ماموجرام', 'name_en' => 'Mammography'],
            ['code' => 'doppler', 'name_ar' => 'دوبلر', 'name_en' => 'Doppler'],
            ['code' => 'echo', 'name_ar' => 'إيكو', 'name_en' => 'Echo'],
            ['code' => 'ecg', 'name_ar' => 'رسم قلب', 'name_en' => 'ECG'],
            ['code' => 'dental_panorama', 'name_ar' => 'بانوراما أسنان', 'name_en' => 'Dental Panorama'],
            ['code' => 'dexa', 'name_ar' => 'قياس كثافة العظام', 'name_en' => 'DEXA Scan'],
        ];
    }
}
