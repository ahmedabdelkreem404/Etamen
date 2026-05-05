# Health, Medication, And Care Plan Mobile Contract

These modules are for tracking and organization only. They do not diagnose, prescribe, or recommend treatment.

## Health Profile

Endpoints:

- `GET /api/v1/health/profile`
- `PUT /api/v1/health/profile`

Flutter can send:

- `date_of_birth`
- `gender`
- `height_cm`
- `weight_kg`
- `blood_type`
- emergency contact fields
- notes

Flutter must not send `patient_user_id`.

## Vitals

Endpoints:

- `GET /api/v1/health/vitals`
- `POST /api/v1/health/vitals`
- `GET /api/v1/health/vitals/{vital}`
- `PUT /api/v1/health/vitals/{vital}`
- `DELETE /api/v1/health/vitals/{vital}`
- `GET /api/v1/health/vitals/trends`
- `GET /api/v1/health/vitals/latest`
- `GET /api/v1/health/summary`

Flutter can send:

- `vital_type`
- `measured_at`
- numeric values
- unit
- notes
- safe metadata such as blood sugar context

Flutter must not send:

- `patient_user_id`
- `flag`
- `source`

Flags are non-diagnostic: `very_low`, `low`, `normal`, `high`, `very_high`, `unknown`.

## Medications

Endpoints:

- reminders CRUD
- reminder times CRUD
- taken/skipped logs
- today/upcoming schedule
- adherence summary
- refill done/skipped

Flutter can send patient-entered reminder text. Flutter must not send `source`, `patient_user_id`, or directly force `status` except through pause/resume/cancel endpoints.

Safety wording:

"تذكيرات الأدوية هدفها التنظيم فقط، ولا تعتبر وصفة طبية أو نصيحة علاجية. لا توقف أو تغير جرعة أي دواء بدون الرجوع للطبيب."

## Care Plans

Endpoints:

- care plan CRUD/status actions
- days/meals/foods/instructions
- check-ins
- meal logs
- progress
- summary

Flutter can send patient-created plan structure for patient-owned plans. Provider-assigned/admin-created plan structure is read-only for patients, but patients can log check-ins/meals while the plan is active.

Flutter must not send:

- `patient_user_id`
- `provider_id` on patient routes
- `assigned_by_user_id`
- `source`
- `status` except through controlled status endpoints

Meal photos are private uploads. No raw path is returned.

Safety wording:

"هذه الخطة للتنظيم والمتابعة ولا تعتبر تشخيصًا أو علاجًا طبيًا. في حالة وجود مرض مزمن أو حمل أو أعراض خطيرة، يجب الرجوع للطبيب أو المختص."

## Summary Handling

Health summaries, adherence summaries, and care plan progress are tracking summaries only. Flutter should avoid wording like "successful treatment" or "medical failure".
