# Provider Staff Permissions

Status: local backend foundation.

This document defines the provider permission model introduced in Sprint 50.

## Compatibility

Existing provider staff roles remain valid:

- `owner`
- `admin`
- `staff`

The new permission layer is additive:

- `provider_staff.permissions` is nullable JSON.
- If it is null, the backend derives permissions from the staff role.
- If it is present, the backend validates every value against `ProviderPermission`.

## Role Rules

Owner:

- Always has all provider permissions.
- Cannot be deleted through staff APIs.
- Cannot have owner permissions removed through staff APIs.

Admin:

- Receives a safe provider-admin default permission set.
- Can manage broad provider operations.
- Cannot grant owner.

Staff:

- Receives limited defaults.
- Can be assigned explicit safe permissions.
- Cannot manage another provider.

## Core Permissions

General:

- `manage_profile`
- `manage_branches`
- `manage_staff`
- `view_reports`

Bookings:

- `view_bookings`
- `manage_bookings`
- `create_bookings`

Payments:

- `view_payments`
- `review_payment_proofs`
- `view_wallet`

Doctor:

- `manage_schedules`
- `manage_slots`
- `view_appointments`
- `manage_appointments`

Hospital:

- `manage_departments`
- `manage_hospital_doctors`
- `view_hospital_reports`

Radiology:

- `manage_radiology_catalog`
- `view_radiology_orders`
- `manage_radiology_orders`
- `upload_radiology_results`

Pharmacy:

- `manage_pharmacy_products`
- `view_pharmacy_orders`
- `manage_pharmacy_orders`
- `review_prescriptions`

Labs:

- `manage_lab_catalog`
- `view_lab_orders`
- `manage_lab_orders`
- `upload_lab_results`

Gym:

- `manage_gym_plans`
- `manage_gym_classes`
- `view_gym_bookings`
- `manage_gym_bookings`

Coach:

- `manage_coach_sessions`
- `manage_coach_availability`
- `view_coach_bookings`
- `manage_coach_bookings`

## Safety Rules

- Flutter never decides if a user is a provider, admin, owner, or staff.
- Provider dashboard APIs require active same-provider staff membership.
- Staff APIs require owner or `manage_staff`.
- Staff cannot be added to a provider by another provider's staff.
- Staff cannot escalate themselves to owner.
- Staff cannot delete the provider owner.
- Private provider documents, bank details, payment configuration, contracts, and admin notes are not returned by workspace or dashboard endpoints.

## Deferred Work

- Invitation flow for non-existing users.
- Fine-grained audit UI for staff changes.
- Full provider portal operations.
- Staff activity reports.
