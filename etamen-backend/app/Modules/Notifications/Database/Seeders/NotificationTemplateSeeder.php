<?php

namespace App\Modules\Notifications\Database\Seeders;

use App\Modules\Notifications\Domain\Enums\NotificationCategory;
use App\Modules\Notifications\Domain\Enums\NotificationChannel;
use App\Modules\Notifications\Infrastructure\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->templates() as $template) {
            NotificationTemplate::query()->updateOrCreate(
                ['key' => $template['key']],
                [
                    ...$template,
                    'channel' => NotificationChannel::InApp,
                    'is_active' => true,
                ],
            );
        }
    }

    private function templates(): array
    {
        return [
            ['key' => 'appointment_booked', 'category' => NotificationCategory::Appointments, 'title_ar' => 'تم حجز الموعد', 'body_ar' => 'تم تسجيل موعدك رقم {{appointment_number}}.'],
            ['key' => 'appointment_confirmed', 'category' => NotificationCategory::Appointments, 'title_ar' => 'تم تأكيد الموعد', 'body_ar' => 'تم تأكيد موعدك رقم {{appointment_number}}.'],
            ['key' => 'appointment_cancelled', 'category' => NotificationCategory::Appointments, 'title_ar' => 'تم إلغاء الموعد', 'body_ar' => 'تم إلغاء موعدك رقم {{appointment_number}}.'],
            ['key' => 'appointment_reminder', 'category' => NotificationCategory::Appointments, 'title_ar' => 'تذكير بالموعد', 'body_ar' => 'لديك موعد قريب رقم {{appointment_number}} خلال {{window}} ساعة.'],
            ['key' => 'appointment_completed', 'category' => NotificationCategory::Appointments, 'title_ar' => 'تم اكتمال الموعد', 'body_ar' => 'تم تسجيل اكتمال موعدك رقم {{appointment_number}}.'],
            ['key' => 'payment_pending_review', 'category' => NotificationCategory::Payments, 'title_ar' => 'دفعة تحتاج مراجعة', 'body_ar' => 'هناك دفعة رقم {{payment_id}} بانتظار مراجعة الإدارة.'],
            ['key' => 'payment_verified', 'category' => NotificationCategory::Payments, 'title_ar' => 'تم تأكيد الدفع', 'body_ar' => 'تم تأكيد الدفع بنجاح.'],
            ['key' => 'payment_rejected', 'category' => NotificationCategory::Payments, 'title_ar' => 'تم رفض الدفع', 'body_ar' => 'تم رفض إثبات الدفع. يمكنك مراجعة التفاصيل داخل التطبيق.'],
            ['key' => 'pharmacy_order_created', 'category' => NotificationCategory::Pharmacy, 'title_ar' => 'طلب صيدلية جديد', 'body_ar' => 'يوجد طلب صيدلية رقم {{order_number}}.'],
            ['key' => 'pharmacy_order_accepted', 'category' => NotificationCategory::Pharmacy, 'title_ar' => 'تم قبول طلب الصيدلية', 'body_ar' => 'تم قبول طلبك رقم {{order_number}}.'],
            ['key' => 'pharmacy_order_paid', 'category' => NotificationCategory::Pharmacy, 'title_ar' => 'تم دفع طلب الصيدلية', 'body_ar' => 'تم تسجيل دفع طلب الصيدلية رقم {{order_number}}.'],
            ['key' => 'pharmacy_order_delivered', 'category' => NotificationCategory::Pharmacy, 'title_ar' => 'تم تسليم طلب الصيدلية', 'body_ar' => 'تم تسجيل تسليم طلبك رقم {{order_number}}.'],
            ['key' => 'pharmacy_order_rejected', 'category' => NotificationCategory::Pharmacy, 'title_ar' => 'تم رفض طلب الصيدلية', 'body_ar' => 'تم رفض طلب الصيدلية رقم {{order_number}}.'],
            ['key' => 'lab_order_accepted', 'category' => NotificationCategory::Labs, 'title_ar' => 'تم قبول طلب المعمل', 'body_ar' => 'تم قبول طلب المعمل رقم {{order_number}}.'],
            ['key' => 'lab_order_paid', 'category' => NotificationCategory::Labs, 'title_ar' => 'تم دفع طلب المعمل', 'body_ar' => 'تم تسجيل دفع طلب المعمل رقم {{order_number}}.'],
            ['key' => 'lab_result_ready', 'category' => NotificationCategory::Labs, 'title_ar' => 'نتيجة المعمل جاهزة', 'body_ar' => 'نتيجة طلب المعمل رقم {{order_number}} جاهزة للعرض الآمن داخل التطبيق.'],
            ['key' => 'lab_order_completed', 'category' => NotificationCategory::Labs, 'title_ar' => 'تم اكتمال طلب المعمل', 'body_ar' => 'تم تسجيل اكتمال طلب المعمل رقم {{order_number}}.'],
            ['key' => 'medication_reminder_due', 'category' => NotificationCategory::Medications, 'title_ar' => 'تذكير دواء', 'body_ar' => 'تذكير تنظيمي بموعد {{medication_name}}. لا تغيّر أي جرعة بدون الرجوع للطبيب.'],
            ['key' => 'medication_missed', 'category' => NotificationCategory::Medications, 'title_ar' => 'جرعة غير مسجلة', 'body_ar' => 'لديك تذكير دواء لم يتم تسجيله. هذا للتنظيم فقط.'],
            ['key' => 'medication_refill_due', 'category' => NotificationCategory::Medications, 'title_ar' => 'تذكير إعادة صرف الدواء', 'body_ar' => 'تذكير تنظيمي بمتابعة توفر الدواء.'],
            ['key' => 'care_plan_assigned', 'category' => NotificationCategory::CarePlans, 'title_ar' => 'تم تعيين خطة متابعة', 'body_ar' => 'تم تعيين خطة متابعة: {{plan_title}}.'],
            ['key' => 'care_plan_checkin_due', 'category' => NotificationCategory::CarePlans, 'title_ar' => 'تذكير متابعة يومية', 'body_ar' => 'تذكير بتسجيل متابعة اليوم لخطة {{plan_title}}. هذا للتنظيم وليس علاجًا.'],
            ['key' => 'withdrawal_requested', 'category' => NotificationCategory::Wallet, 'title_ar' => 'طلب سحب جديد', 'body_ar' => 'تم تسجيل طلب السحب رقم {{withdrawal_id}}.'],
            ['key' => 'withdrawal_approved', 'category' => NotificationCategory::Wallet, 'title_ar' => 'تم قبول طلب السحب', 'body_ar' => 'تم قبول طلب السحب رقم {{withdrawal_id}}.'],
            ['key' => 'withdrawal_rejected', 'category' => NotificationCategory::Wallet, 'title_ar' => 'تم رفض طلب السحب', 'body_ar' => 'تم رفض طلب السحب رقم {{withdrawal_id}}.'],
            ['key' => 'withdrawal_paid', 'category' => NotificationCategory::Wallet, 'title_ar' => 'تم دفع طلب السحب', 'body_ar' => 'تم تسجيل دفع طلب السحب رقم {{withdrawal_id}}.'],
            ['key' => 'ai_red_flag_admin_alert', 'category' => NotificationCategory::AiSafety, 'title_ar' => 'تنبيه أمان AI', 'body_ar' => 'يوجد حدث أمان AI عالي/حرج رقم {{event_id}} يحتاج مراجعة.'],
            ['key' => 'ai_provider_error_admin_alert', 'category' => NotificationCategory::AiSafety, 'title_ar' => 'خطأ في مزود AI', 'body_ar' => 'يوجد خطأ في مزود AI يحتاج متابعة تشغيلية.'],
            ['key' => 'system_notice', 'category' => NotificationCategory::System, 'title_ar' => 'إشعار من اتطمن', 'body_ar' => '{{body}}'],
        ];
    }
}
