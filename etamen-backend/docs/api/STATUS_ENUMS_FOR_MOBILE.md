# Status Enums For Mobile

Flutter should model these as enums with a safe fallback such as `unknown`.

## Appointments

| Value | Arabic | English | UI |
| --- | --- | --- | --- |
| `draft` | مسودة | Draft | neutral |
| `pending_payment` | بانتظار الدفع | Pending payment | warning |
| `pending_payment_review` | بانتظار مراجعة الدفع | Payment review | warning |
| `confirmed` | مؤكد | Confirmed | success |
| `accepted` | مقبول من الطبيب | Accepted | success |
| `rejected` | مرفوض | Rejected | danger |
| `cancelled_by_patient` | ملغي من المريض | Cancelled by patient | neutral |
| `cancelled_by_doctor` | ملغي من الطبيب | Cancelled by doctor | neutral |
| `completed` | مكتمل | Completed | success |
| `no_show` | لم يحضر | No show | warning |
| `expired` | منتهي | Expired | neutral |

## Payments

| Value | Arabic | English | UI |
| --- | --- | --- | --- |
| `draft` | مسودة | Draft | neutral |
| `awaiting_method` | اختر طريقة الدفع | Awaiting method | info |
| `awaiting_proof` | بانتظار إثبات الدفع | Awaiting proof | warning |
| `pending_review` | تحت المراجعة | Pending review | warning |
| `pending_gateway` | جاري الدفع الإلكتروني | Pending gateway | warning |
| `verified` | مؤكد الدفع | Verified | success |
| `rejected` | مرفوض | Rejected | danger |
| `failed` | فشل | Failed | danger |
| `expired` | منتهي | Expired | neutral |
| `cancelled` | ملغي | Cancelled | neutral |
| `refunded` | مسترد | Refunded | info |

## Pharmacy Orders

Order statuses: `pending`, `pharmacy_review`, `accepted`, `rejected`, `awaiting_payment`, `paid`, `preparing`, `ready_for_pickup`, `out_for_delivery`, `delivered`, `cancelled`.

Payment statuses: `unpaid`, `pending_payment`, `pending_payment_review`, `paid`, `failed`, `refunded`.

Recommended UI: accepted/paid/delivered = success, review/payment/preparing/delivery = warning/info, rejected/cancelled/failed = danger or neutral.

## Lab Orders

Order statuses: `lab_review`, `accepted`, `rejected`, `awaiting_payment`, `paid`, `sample_scheduled`, `sample_collected`, `processing`, `result_ready`, `completed`, `cancelled`.

Payment statuses: `unpaid`, `pending_payment`, `pending_payment_review`, `paid`, `failed`, `refunded`.

Recommended UI: paid/result_ready/completed = success, lab_review/awaiting_payment/processing = warning/info, rejected/cancelled/failed = danger or neutral.

## Wallet And Settlements

Wallet transaction types: `hold`, `release`, `commission`, `withdrawal`, `reversal`.

Wallet transaction statuses: `pending`, `posted`, `reversed`, `cancelled`.

Withdrawal statuses: `pending`, `approved`, `rejected`, `paid`, `cancelled`.

Settlement statuses: `draft`, `approved`, `paid`, `cancelled`.

## Health

Vital types: `blood_pressure`, `blood_sugar`, `heart_rate`, `oxygen_saturation`, `temperature`, `weight`, `sleep`, `mood`, `symptom`.

Flags: `very_low`, `low`, `normal`, `high`, `very_high`, `unknown`.

These flags are non-diagnostic only.

## Medications

Reminder statuses: `active`, `paused`, `completed`, `cancelled`.

Frequency types: `once_daily`, `twice_daily`, `three_times_daily`, `custom_times`, `every_x_hours`, `specific_days`, `as_needed`.

Log actions: `taken`, `skipped`, `missed`.

## Care Plans

Plan statuses: `draft`, `active`, `paused`, `completed`, `cancelled`.

Plan types: `nutrition`, `general_care`, `weight_management`, `diabetes_followup`, `blood_pressure_followup`, `fitness_followup`, `recovery_followup`, `other`.

Meal log statuses: `followed`, `partially_followed`, `skipped`, `replaced`, `extra_meal`.

## AI

Conversation statuses: `active`, `archived`, `blocked`.

Safety classifications: `safe`, `medical_advice_request`, `diagnosis_request`, `medication_change_request`, `emergency_red_flag`, `mental_health_crisis`, `unsafe`, `unknown`.

Unsafe/red-flag responses are local backend safety responses, not provider-generated medical advice.

## Notifications

Dispatch statuses: `pending`, `queued`, `sent`, `failed`, `cancelled`, `skipped`.

Categories: `appointments`, `payments`, `pharmacy`, `labs`, `medications`, `care_plans`, `wallet`, `ai_safety`, `system`.

Priorities: `low`, `normal`, `high`, `urgent`.
