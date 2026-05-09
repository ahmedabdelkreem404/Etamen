# Sprint 43 Hospital Booking Context

Date: 2026-05-09  
Scope: Local only

## Goal

Sprint 43 preserves backend-owned hospital context when a patient books a doctor after discovering that doctor through:

```text
Hospitals -> Hospital details -> Department -> Doctor -> Booking
```

This sprint does not touch Hostinger, staging, SSH, deployment, radiology, gyms, coaches, or public launch readiness.

## Database Fields

Added nullable appointment context fields:

- `appointments.hospital_provider_id`
- `appointments.hospital_department_id`
- `appointments.hospital_doctor_id`

Indexes added:

- `hospital_provider_id`
- `hospital_department_id`
- `hospital_doctor_id`
- `hospital_provider_id, status`
- `hospital_provider_id, booked_at`

Direct doctor bookings keep all three fields null.

## Backend Validation

The Flutter app may send hospital context as a hint, but the backend validates it before booking:

- hospital provider must be type `hospital`.
- hospital must be approved and active.
- department must belong to the hospital.
- department must be active.
- doctor must be linked to the hospital through `hospital_doctors`.
- hospital doctor link must be active.
- doctor provider must be approved and active.
- selected `doctor_profile_id` must belong to the linked doctor provider.
- selected slot must belong to the same doctor.
- invalid context returns 422 and does not create appointment, reserve slot, or create payment.

## Price Policy

Backend price remains authoritative:

1. If valid hospital context exists and `hospital_doctors.consultation_fee` is not null, use it.
2. Otherwise use `doctor_profiles.consultation_fee`.
3. Flutter cannot force price.

Status history metadata stores the price source for audit/debugging.

## Appointment API Response

Patient appointment resources may safely include:

- `booked_through_hospital`
- `hospital_provider_id`
- `hospital_department_id`
- `hospital_doctor_id`
- safe hospital id/name
- safe department id/name

They do not expose:

- provider private documents
- storage paths
- tax/commercial/bank data
- contract terms
- admin notes

## Admin Reporting Foundation

Added admin endpoints:

```text
GET /api/v1/admin/hospitals/{hospital}/appointments
GET /api/v1/admin/hospitals/{hospital}/summary
```

Summary includes:

- total appointments
- pending payment
- pending payment review
- confirmed
- completed
- cancelled
- gross amount
- verified paid amount
- pending amount

Payment proof files are not exposed in the summary.

## Tests

Added `tests/Feature/HospitalBookingContextSprint43Test.php`.

Covered:

- direct booking still works with null context.
- valid hospital booking stores context.
- hospital fee overrides doctor fee.
- null hospital fee falls back to doctor fee.
- invalid hospital/department/doctor context is rejected.
- pending hospitals and inactive links are rejected.
- slot must belong to selected doctor.
- frontend price cannot be forced.
- patient response is safe.
- admin hospital summary counts context appointments.

## Remaining Work

- Staging must be tested separately.
- Physical-device proof upload/admin review remains outside this sprint.
- Provider-facing hospital reports can be expanded later.
- Hospital-specific invoice/settlement reporting can be deepened after real operational requirements are confirmed.

## Local QA Evidence

Local backend:

```text
php artisan migrate:fresh --seed
php artisan db:seed --class=PilotDemoSeeder
php artisan etamen:ensure-payment-methods --staging
php artisan serve --host=0.0.0.0 --port=8000
```

Emulator API:

```text
http://10.0.2.2:8000/api/v1
```

Observed result:

- Hospital booking stored `hospital_provider_id`, `hospital_department_id`, and `hospital_doctor_id`.
- Patient appointment response included safe hospital/department names.
- The pediatrics hospital doctor fee `240.00 EGP` was used instead of trusting Flutter.
- Admin summary counted one hospital appointment with pending amount `240.00`.

Screenshots/evidence:

```text
I:\Etamen\.tmp\sprint43-local-hospital-context\
```

## Final Tests

```text
php artisan test
```

Result:

```text
PASS
```

## Decision

```text
LOCAL_HOSPITAL_CONTEXT_ACCEPTED
```

This is local-only and does not approve staging, public launch, or physical-device pilot readiness.
