# Mobile DTO Examples

These examples are intentionally stable, compact DTO references for Flutter model generation. Nullable fields should be represented as nullable Dart fields.

## AuthUser

```json
{
  "id": 1,
  "name": "Ahmed Patient",
  "email": "patient@example.com",
  "phone": "01012345678",
  "roles": ["patient"],
  "created_at": "2026-05-05T10:00:00.000000Z"
}
```

Enum fields: `roles`.

## DoctorListItem

```json
{
  "id": 10,
  "type": "doctor",
  "name_ar": "د. أحمد",
  "name_en": "Dr Ahmed",
  "slug": "dr-ahmed",
  "status": "approved",
  "is_active": true,
  "doctor_profile": {
    "id": 7,
    "specialty_id": 2,
    "consultation_fee": "250.00",
    "rating_average": "4.80",
    "rating_count": 12
  }
}
```

## DoctorProfile

```json
{
  "id": 10,
  "type": "doctor",
  "name_ar": "د. أحمد",
  "name_en": "Dr Ahmed",
  "branches": [],
  "doctor_profile": {
    "id": 7,
    "bio_ar": null,
    "bio_en": "Cardiology consultant",
    "consultation_fee": "250.00",
    "rating_average": "4.80",
    "rating_count": 12
  }
}
```

## Appointment

```json
{
  "id": 55,
  "appointment_number": "APT-20260505-0001",
  "patient_user_id": 1,
  "doctor_profile_id": 7,
  "provider_id": 10,
  "appointment_slot_id": 88,
  "consultation_type": "clinic",
  "problem_description": "Headache",
  "price": "250.00",
  "currency": "EGP",
  "status": "pending_payment",
  "payment_id": 200,
  "booked_at": "2026-05-05T10:10:00.000000Z"
}
```

Enum fields: `consultation_type`, `status`.

## Payment

```json
{
  "id": 200,
  "method_type": "manual_vodafone_cash",
  "amount": "250.00",
  "currency": "EGP",
  "status": "pending_review",
  "payable": {
    "type": "appointment",
    "id": 55,
    "summary": "APT-20260505-0001"
  },
  "appointment": {
    "id": 55,
    "status": "pending_payment_review"
  },
  "invoice": null,
  "expires_at": null,
  "verified_at": null,
  "rejected_at": null
}
```

Sensitive gateway payloads are intentionally absent.

## PaymentMethod

```json
{
  "id": 1,
  "type": "manual_vodafone_cash",
  "name_ar": "فودافون كاش",
  "name_en": "Vodafone Cash",
  "instructions_ar": "حوّل على الرقم المسجل ثم ارفع الإثبات.",
  "instructions_en": "Transfer then upload proof.",
  "is_active": true
}
```

Allowed `type`: `paymob`, `manual_vodafone_cash`, `manual_instapay`.

## PharmacyProduct

```json
{
  "id": 44,
  "provider_id": 20,
  "name_ar": null,
  "name_en": "Vitamin C",
  "description_ar": null,
  "description_en": "1000mg",
  "price": "120.00",
  "currency": "EGP",
  "requires_prescription": false,
  "stock_quantity": 20,
  "sku": "VC-1000",
  "is_active": true
}
```

Flutter must not send price or stock when creating patient orders.

## PharmacyOrder

```json
{
  "id": 90,
  "order_number": "PH-20260505-0001",
  "patient_user_id": 1,
  "pharmacy_provider_id": 20,
  "subtotal": "180.00",
  "discount_total": "0.00",
  "grand_total": "180.00",
  "currency": "EGP",
  "payment_status": "pending_payment",
  "order_status": "awaiting_payment",
  "delivery_method": "pickup",
  "delivery_address": null,
  "payment_id": 210,
  "items": [
    {
      "product_id": 44,
      "product_name": "Vitamin C",
      "unit_price": "120.00",
      "quantity": 1,
      "line_total": "120.00"
    }
  ]
}
```

Patient responses do not include `commission_amount` or `provider_net_amount`. Provider/admin responses may include them.

## LabTest

```json
{
  "id": 30,
  "provider_id": 25,
  "name_ar": "صورة دم كاملة",
  "name_en": "CBC",
  "code": "CBC",
  "price": "150.00",
  "sample_type": "blood",
  "result_time_hours": 24,
  "is_active": true
}
```

## LabOrder

```json
{
  "id": 77,
  "order_number": "LAB-20260505-0001",
  "patient_user_id": 1,
  "lab_provider_id": 25,
  "subtotal": "150.00",
  "discount_total": "0.00",
  "grand_total": "150.00",
  "currency": "EGP",
  "payment_status": "unpaid",
  "order_status": "lab_review",
  "sample_collection_method": "branch_visit",
  "collection_address": null,
  "payment_id": null
}
```

Patient responses do not include lab commission or provider net amount.

## LabResult

```json
{
  "id": 11,
  "order_id": 77,
  "title_ar": "نتيجة التحليل",
  "title_en": "Lab result",
  "status": "visible_to_patient",
  "file": {
    "id": 70,
    "original_name": "result.pdf",
    "mime_type": "application/pdf",
    "size": 100000,
    "visibility": "private"
  },
  "created_at": "2026-05-05T12:00:00.000000Z"
}
```

No raw path or public URL is returned.

## HealthProfile

```json
{
  "id": 8,
  "patient_user_id": 1,
  "date_of_birth": "1990-01-10",
  "gender": "male",
  "height_cm": "178.00",
  "weight_kg": "82.00",
  "blood_type": "O+",
  "notes": null
}
```

Flutter must not send `patient_user_id`.

## VitalRecord

```json
{
  "id": 99,
  "patient_user_id": 1,
  "vital_type": "blood_pressure",
  "measured_at": "2026-05-05T08:00:00.000000Z",
  "value_decimal": "130.00",
  "value_secondary_decimal": "85.00",
  "unit": "mmHg",
  "flag": "normal",
  "source": "manual",
  "notes": "بعد المشي",
  "metadata": {}
}
```

`flag` and `source` are backend-calculated/controlled.

## MedicationReminder

```json
{
  "id": 14,
  "patient_user_id": 1,
  "medication_name": "Medication name",
  "dosage": "1 tablet",
  "frequency_type": "once_daily",
  "start_date": "2026-05-05",
  "end_date": null,
  "timezone": "Africa/Cairo",
  "status": "active",
  "source": "patient_entered",
  "times": [
    { "id": 1, "time_of_day": "08:00:00", "label": "Morning", "is_active": true }
  ],
  "disclaimer": "تذكيرات الأدوية للتنظيم فقط وليست وصفة طبية أو نصيحة علاجية."
}
```

## MedicationLog

```json
{
  "id": 33,
  "medication_reminder_id": 14,
  "patient_user_id": 1,
  "scheduled_for": "2026-05-05T08:00:00.000000Z",
  "action": "taken",
  "taken_at": "2026-05-05T08:05:00.000000Z",
  "notes": null
}
```

## CarePlan

```json
{
  "id": 21,
  "patient_user_id": 1,
  "assigned_by_user_id": null,
  "provider_id": null,
  "plan_type": "nutrition",
  "title": "General nutrition follow-up",
  "start_date": "2026-05-05",
  "end_date": null,
  "status": "active",
  "visibility": "patient_only",
  "source": "patient_created",
  "safety_disclaimer": "هذه الخطة للتنظيم والمتابعة ولا تعتبر تشخيصًا أو علاجًا طبيًا."
}
```

## MealLog

```json
{
  "id": 66,
  "care_plan_id": 21,
  "care_plan_meal_id": null,
  "patient_user_id": 1,
  "logged_at": "2026-05-05T14:00:00.000000Z",
  "meal_type": "lunch",
  "status": "followed",
  "description": "Grilled chicken and salad",
  "photo": {
    "id": 81,
    "original_name": "meal.jpg",
    "visibility": "private"
  },
  "notes": null
}
```

## AiConversation

```json
{
  "id": 9,
  "patient_user_id": 1,
  "title": "تنظيم قراءات الضغط",
  "status": "active",
  "provider": "fake",
  "language": "ar",
  "context_enabled": true,
  "safety_level": "standard",
  "last_message_at": "2026-05-05T13:00:00.000000Z"
}
```

## AiMessage

```json
{
  "id": 40,
  "conversation_id": 9,
  "patient_user_id": 1,
  "role": "assistant",
  "content": "أقدر أساعدك في تنظيم الأسئلة للطبيب بدون تشخيص.",
  "safety_classification": "safe",
  "was_refused": false,
  "provider": "fake",
  "token_count": 120,
  "metadata": {
    "model": "fake",
    "context_included": true
  }
}
```

Raw provider response and secrets are not exposed.

## Notification

```json
{
  "id": 120,
  "user_id": 1,
  "category": "appointments",
  "type": "appointment_confirmed",
  "title": "تم تأكيد الموعد",
  "body": "تم تأكيد موعدك.",
  "data": {
    "appointment_id": 55
  },
  "priority": "normal",
  "read_at": null,
  "action_url": null,
  "created_at": "2026-05-05T13:20:00.000000Z"
}
```

Push payloads are even smaller than in-app details and must not include private medical text, raw file paths, API keys, commission, or provider net earnings.
