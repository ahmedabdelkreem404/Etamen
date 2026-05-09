# Sprint 43 Local Hospital Context QA

Date: 2026-05-09  
Scope: Local emulator only

## Backend URL

```text
http://10.0.2.2:8000/api/v1
```

## Flutter Changes

Added hospital context passing from:

```text
Hospitals -> Hospital details -> Department doctors -> Doctor profile -> Booking
```

The app passes only optional context IDs:

- `hospital_provider_id`
- `hospital_department_id`
- `hospital_doctor_id` if available later

Normal doctor bookings from the Doctors tab send no hospital context.

## UI Behavior

Doctor profile now shows a hospital context card when opened from a hospital department.

Booking page now shows a hospital context card before confirmation.

My appointments and appointment details can show a friendly hospital badge/context when the backend returns it.

## Safety

Flutter does not:

- set price.
- set status.
- verify payment.
- create hospital/doctor links.
- trust hospital context as truth.

The backend validates the context and returns 422 if invalid.

## Local QA Status

Local emulator QA was completed against:

```text
http://10.0.2.2:8000/api/v1
```

APK:

```text
I:\Etamen\.tmp\etamen-local-hospital-context.apk
C:\Users\Ahmed Abdelkareem\OneDrive\Desktop\Etamen_Android_Website_Ready\etamen-local-hospital-context.apk
```

SHA-256:

```text
DDCC48D779BDF4CA314B52482AE3EB553F35379E9BDA020DD0EAF08F5565B67E
```

Checklist:

| Flow | Result |
| --- | --- |
| Login | PASS |
| Hospitals list | PASS |
| Hospital details | PASS |
| Department doctors | PASS |
| Doctor profile with hospital context | PASS |
| Booking with hospital context | PASS |
| Payment methods | PASS |
| My appointments with hospital context | PASS |
| Admin hospital summary | PASS |
| Context remains after refresh | PASS |

Observed local booking:

- Appointment id: local QA evidence only.
- Hospital context: `مستشفى اطمن التخصصي - قسم أطفال`.
- Stored context fields: `hospital_provider_id`, `hospital_department_id`, `hospital_doctor_id`.
- Price shown in app: `240.00 EGP`.
- Price source: hospital doctor consultation fee, not frontend input.

## Screenshots

Path:

```text
I:\Etamen\.tmp\sprint43-local-hospital-context\
```

Key files:

- `01-home.png`
- `02-hospitals-list.png`
- `03-hospital-details.png`
- `04-department-doctors.png`
- `05-doctor-profile-with-hospital-context.png`
- `06-booking-with-hospital-context.png`
- `08-payment-methods.png`
- `07-my-appointments-with-hospital-context.png`
- `08-admin-hospital-summary.png`
- `08-admin-hospital-summary.json`
- `09-after-refresh-context-still-visible.png`

## Decision

```text
LOCAL_HOSPITAL_CONTEXT_ACCEPTED
```

This is local-only. It does not approve staging, public launch, or physical-device pilot readiness.
