# Hospital Foundation

Date: 2026-05-08  
Sprint: 36 - Unified Provider Platform Foundation

## What Was Added

Hospitals are now supported as a provider type:
- `hospital`

Added:
- `hospital_profiles`
- `hospital_departments`
- `hospital_doctors`

## Hospital Profile

`hospital_profiles` supports:
- license number
- Arabic/English description
- emergency availability
- inpatient/outpatient flags
- ICU availability
- ambulance availability
- active flag

## Departments

`hospital_departments` supports:
- hospital provider
- optional specialty
- Arabic/English name
- Arabic/English description
- active flag

Validation:
- `hospital_provider_id` must reference a provider of type `hospital`.

## Hospital Doctors

`hospital_doctors` links:
- hospital provider
- doctor provider
- optional hospital department
- consultation fee
- online consultation flag for later
- clinic consultation flag
- active flag

Validation:
- hospital provider must be type `hospital`.
- doctor provider must be type `doctor`.
- doctor can remain independent and also belong to hospitals.

## Public Visibility

Public scopes require:
- hospital approved and active.
- doctor approved and active.
- link active.

---

## Sprint 43 Update - Appointment Context

Sprint 43 added backend-owned hospital booking context to appointments.

When a patient books a doctor after discovering the doctor through a hospital department, the appointment can now store:

- `hospital_provider_id`
- `hospital_department_id`
- `hospital_doctor_id`

Direct doctor bookings leave these fields null.

The backend validates the full relationship before booking:

- hospital must be type `hospital`, approved, and active.
- department must belong to that hospital and be active.
- doctor must be linked through an active `hospital_doctors` row.
- selected slot must belong to the selected doctor.

The patient appointment API returns only safe hospital and department names. It does not expose private documents, storage paths, admin notes, or contract/payment terms.

Admin reporting foundation was added:

```text
GET /api/v1/admin/hospitals/{hospital}/appointments
GET /api/v1/admin/hospitals/{hospital}/summary
```

This is still local/backend foundation work and does not approve staging or public launch.

No patient-facing hospital public page was implemented in Sprint 36.

## Remaining Work

Future sprints:
- hospital public profile API.
- department listing API.
- hospital doctor schedules.
- hospital appointment booking rules.
- hospital admin operations.
- Flutter patient UI only after complete backend lifecycle.
