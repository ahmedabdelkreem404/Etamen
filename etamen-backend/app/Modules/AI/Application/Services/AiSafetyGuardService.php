<?php

namespace App\Modules\AI\Application\Services;

use App\Modules\AI\Application\DTOs\AiSafetyDecision;
use App\Modules\AI\Domain\Enums\AiLanguage;
use App\Modules\AI\Domain\Enums\AiSafetyClassification;
use App\Modules\AI\Domain\Enums\AiSafetyEventType;
use App\Modules\AI\Domain\Enums\AiSafetySeverity;

class AiSafetyGuardService
{
    public const AR_SAFETY_LINE = 'أنا لست طبيبًا ولا أستطيع التشخيص أو وصف علاج. أقدر أساعدك في تنظيم المعلومات وفهمها بشكل عام، لكن القرار الطبي لازم يكون مع طبيب مختص.';

    public const AR_EMERGENCY_LINE = 'لو عندك أعراض خطيرة مثل ألم صدر شديد، ضيق تنفس، فقدان وعي، نزيف شديد، ضعف مفاجئ في جانب من الجسم، أو أفكار لإيذاء النفس، تواصل مع الطوارئ فورًا.';

    public const EN_SAFETY_LINE = 'I am not a doctor and cannot diagnose or prescribe treatment. I can help organize and understand information generally, but medical decisions must be made with a qualified clinician.';

    public const EN_EMERGENCY_LINE = 'If you have severe symptoms such as severe chest pain, shortness of breath, loss of consciousness, severe bleeding, sudden weakness on one side, or thoughts of self-harm, contact emergency services immediately.';

    public function inspect(string $content, AiLanguage|string $language = AiLanguage::Arabic): AiSafetyDecision
    {
        $language = $language instanceof AiLanguage ? $language : AiLanguage::from($language);
        $text = mb_strtolower($content);

        if ($this->containsAny($text, $this->mentalHealthCrisisKeywords())) {
            return new AiSafetyDecision(
                classification: AiSafetyClassification::MentalHealthCrisis,
                shouldRefuse: true,
                shouldCallProvider: false,
                safeResponse: $this->emergencyResponse($language),
                eventType: AiSafetyEventType::EmergencyGuidance,
                severity: AiSafetySeverity::Critical,
            );
        }

        if ($this->containsAny($text, $this->emergencyKeywords())) {
            return new AiSafetyDecision(
                classification: AiSafetyClassification::EmergencyRedFlag,
                shouldRefuse: true,
                shouldCallProvider: false,
                safeResponse: $this->emergencyResponse($language),
                eventType: AiSafetyEventType::RedFlagDetected,
                severity: AiSafetySeverity::Critical,
            );
        }

        if ($this->containsAny($text, $this->medicationUnsafeKeywords())) {
            return new AiSafetyDecision(
                classification: AiSafetyClassification::MedicationChangeRequest,
                shouldRefuse: true,
                shouldCallProvider: false,
                safeResponse: $this->medicationRefusal($language),
                eventType: AiSafetyEventType::MedicationSafety,
                severity: AiSafetySeverity::High,
            );
        }

        if ($this->containsAny($text, $this->diagnosisKeywords())) {
            return new AiSafetyDecision(
                classification: AiSafetyClassification::DiagnosisRequest,
                shouldRefuse: true,
                shouldCallProvider: false,
                safeResponse: $this->diagnosisRefusal($language),
                eventType: AiSafetyEventType::DiagnosisSafety,
                severity: AiSafetySeverity::High,
            );
        }

        if ($this->containsAny($text, $this->treatmentKeywords())) {
            return new AiSafetyDecision(
                classification: AiSafetyClassification::MedicalAdviceRequest,
                shouldRefuse: true,
                shouldCallProvider: false,
                safeResponse: $this->treatmentRefusal($language),
                eventType: AiSafetyEventType::RefusalTriggered,
                severity: AiSafetySeverity::High,
            );
        }

        return new AiSafetyDecision(
            classification: AiSafetyClassification::Safe,
            shouldRefuse: false,
            shouldCallProvider: true,
        );
    }

    public function safetyLine(AiLanguage $language): string
    {
        return $language === AiLanguage::English ? self::EN_SAFETY_LINE : self::AR_SAFETY_LINE;
    }

    private function emergencyResponse(AiLanguage $language): string
    {
        if ($language === AiLanguage::English) {
            return self::EN_EMERGENCY_LINE.' '.self::EN_SAFETY_LINE;
        }

        return self::AR_EMERGENCY_LINE.' '.self::AR_SAFETY_LINE;
    }

    private function medicationRefusal(AiLanguage $language): string
    {
        if ($language === AiLanguage::English) {
            return 'I cannot tell you to stop, start, change, or adjust a medication dose. Please speak with your doctor or pharmacist. I can help you organize your medication list or prepare questions for them. '.self::EN_SAFETY_LINE;
        }

        return 'لا أستطيع أن أنصحك بإيقاف دواء أو تغيير جرعة أو وصف مضاد حيوي. راجع طبيبك أو الصيدلي، وأقدر أساعدك في تنظيم قائمة الأدوية أو تجهيز أسئلة للطبيب. '.self::AR_SAFETY_LINE;
    }

    private function diagnosisRefusal(AiLanguage $language): string
    {
        if ($language === AiLanguage::English) {
            return 'I cannot diagnose your condition or confirm a disease. I can help organize your symptoms, vitals, and questions so you can discuss them with a doctor. '.self::EN_SAFETY_LINE;
        }

        return 'لا أستطيع تشخيص حالتك أو تأكيد وجود مرض. أقدر أساعدك في ترتيب الأعراض والقراءات والأسئلة التي تناقشها مع الطبيب. '.self::AR_SAFETY_LINE;
    }

    private function treatmentRefusal(AiLanguage $language): string
    {
        if ($language === AiLanguage::English) {
            return 'I cannot create a treatment plan or tell you what medicine to take. I can explain general concepts safely and help prepare questions for a qualified clinician. '.self::EN_SAFETY_LINE;
        }

        return 'لا أستطيع وضع خطة علاج أو تحديد دواء مناسب لك. أقدر أوضح معلومات عامة بأمان وأساعدك تجهز أسئلة لطبيب مختص. '.self::AR_SAFETY_LINE;
    }

    private function containsAny(string $text, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function emergencyKeywords(): array
    {
        return [
            'ألم صدر شديد',
            'الم صدر شديد',
            'ضيق تنفس',
            'فقدان وعي',
            'فقدت الوعي',
            'نزيف شديد',
            'جلطة',
            'ضعف مفاجئ',
            'جانب من الجسم',
            'تشنجات',
            'تيبس رقبة',
            'قيء دم',
            'براز أسود',
            'chest pain',
            'shortness of breath',
            'fainting',
            'fainted',
            'stroke symptoms',
            'severe bleeding',
            'seizure',
            'vomiting blood',
            'black stool',
        ];
    }

    private function mentalHealthCrisisKeywords(): array
    {
        return [
            'أفكار انتحار',
            'افكار انتحار',
            'عايز أؤذي نفسي',
            'عايز اؤذي نفسي',
            'أذي نفسي',
            'اؤذي نفسي',
            'انتحر',
            'أنتحر',
            'suicidal thoughts',
            'kill myself',
            'harm myself',
            'hurt myself',
            'self harm',
            'harm others',
        ];
    }

    private function medicationUnsafeKeywords(): array
    {
        return [
            'أوقف الدواء',
            'اوقف الدواء',
            'أوقف دواء',
            'اوقف دواء',
            'أوقف العلاج',
            'اوقف العلاج',
            'أزود الجرعة',
            'ازود الجرعة',
            'أزود جرعة',
            'ازود جرعة',
            'أقلل الجرعة',
            'اقلل الجرعة',
            'أقلل جرعة',
            'اقلل جرعة',
            'زود الجرعة',
            'زوّد الجرعة',
            'قلل الجرعة',
            'اكتبلي مضاد حيوي',
            'اكتب لي مضاد حيوي',
            'خذ مضاد حيوي',
            'خد مضاد حيوي',
            'خد كام قرص',
            'آخد كام قرص',
            'اخد كام قرص',
            'stop my medication',
            'change dose',
            'change my dose',
            'increase dose',
            'decrease dose',
            'prescribe antibiotic',
            'antibiotic prescription',
        ];
    }

    private function diagnosisKeywords(): array
    {
        return [
            'شخصني',
            'شخّصني',
            'عندي إيه',
            'عندي ايه',
            'أنت عندك مرض',
            'انت عندك مرض',
            'نتيجة التحليل',
            'هل عندي سرطان',
            'هل عندي مرض',
            'diagnose me',
            'what disease do i have',
            'do i have cancer',
            'what condition do i have',
        ];
    }

    private function treatmentKeywords(): array
    {
        return [
            'عالجني',
            'اكتبلي علاج',
            'اكتب لي علاج',
            'علاج الضغط',
            'علاج السكر',
            'خطة علاج',
            'treatment plan',
            'what medicine should i take',
            'what medication should i take',
            'give me medicine',
        ];
    }
}
