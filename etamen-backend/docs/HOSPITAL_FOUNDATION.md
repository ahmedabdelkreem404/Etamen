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

No patient-facing hospital public page was implemented in Sprint 36.

## Remaining Work

Future sprints:
- hospital public profile API.
- department listing API.
- hospital doctor schedules.
- hospital appointment booking rules.
- hospital admin operations.
- Flutter patient UI only after complete backend lifecycle.

