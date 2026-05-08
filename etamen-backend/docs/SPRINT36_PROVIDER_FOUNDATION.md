# Sprint 36 Provider Foundation

Date: 2026-05-08  
Sprint: 36 - Unified Provider Platform Foundation

## What Was Implemented

Sprint 36 adds the first safe backend foundation for an Etamen Health Super App provider platform.

Implemented provider types:
- `doctor`
- `pharmacy`
- `lab`
- `hospital`
- `clinic`
- `medical_center`
- `radiology`
- `gym`
- `fitness_coach`
- `nutrition_coach`
- `physiotherapy`
- `home_healthcare`

Existing public MVP flows remain unchanged:
- doctors
- pharmacies
- labs
- appointments
- payments
- wallets
- health
- medications
- care plans
- notifications
- AI

## Internal-Only Provider Types

The following provider types are now valid for onboarding/admin foundation, but they are not exposed as patient-facing modules yet:
- hospitals
- clinics
- medical centers
- radiology centers
- gyms
- fitness coaches
- nutrition coaches
- physiotherapy centers
- home healthcare providers

No Flutter patient links or screens were added for these future modules.

## New Profile Tables

Added minimal type-specific profile tables:
- `hospital_profiles`
- `clinic_profiles`
- `medical_center_profiles`
- `radiology_profiles`
- `gym_profiles`
- `coach_profiles`
- `physiotherapy_profiles`
- `home_healthcare_profiles`

Each table is intentionally small and tied to the common `providers` table through `provider_id`.

## Branch/Location Hardening

`provider_branches` now supports richer safe location fields:
- `address_line_1`
- `address_line_2`
- `district`
- `whatsapp`
- `working_hours_json`
- `is_24_hours`
- `home_service_radius_km`
- `delivery_radius_km`

Existing branch fields were preserved:
- `city_id`
- `area_id`
- `phone`
- `address_ar`
- `address_en`
- `latitude`
- `longitude`
- `is_active`

Public approved provider responses can include safe address/map summary fields. Pending/rejected/suspended providers remain hidden.

## Document Visibility

Provider documents now include:
- `document_type`
- `visibility`
- `status`
- `approved_public_at`

Supported document types:
- `national_id`
- `medical_license`
- `syndicate_card`
- `certificate`
- `tax_card`
- `commercial_register`
- `facility_license`
- `gym_license`
- `coach_certificate`
- `radiology_license`
- `lab_license`
- `pharmacy_license`
- `license` for backwards-compatible legacy uploads
- `other`

Visibility modes:
- `admin_only`
- `public_certificate`

National ID, tax card, and commercial register documents are always forced to `admin_only`.

## Onboarding Workflow

Provider status now includes:
- `draft`
- `pending_review`
- `needs_changes`
- `approved`
- `rejected`
- `suspended`

Added generic onboarding endpoint for future provider types:
- `POST /api/v1/providers/register`

Legacy onboarding endpoints remain:
- `POST /api/v1/providers/register-doctor`
- `POST /api/v1/providers/register-pharmacy`
- `POST /api/v1/providers/register-lab`

## Service Catalog Foundation

Added:
- `service_categories`
- `provider_services`

Provider services are internal/provider/admin foundation. Provider owners can create/update safe service metadata, but cannot set `base_price` through provider API. Admin/ops can manage pricing through admin resources.

## Booking Settings Foundation

Added:
- `provider_booking_settings`

Safe public flags can now express whether a provider supports:
- clinic visit
- online video later
- home visit
- branch visit
- payment required
- pay at branch, only when contract allows it

No video engine or video links were implemented.

## Contract Foundation

Added:
- `provider_contracts`

Supported contract types:
- `commission_only`
- `subscription_only`
- `hybrid`
- `custom`

Supported settlement cycles:
- `daily`
- `weekly`
- `biweekly`
- `monthly`

This does not replace existing wallet/commission behavior yet. It is an admin/ops foundation for future monetization rules.

## Public API Safety

Public provider responses may include:
- provider type/name/status-safe summary
- approved branch/address/map summary
- safe booking capability flags
- safe payment option flags
- approved public certificate metadata only
- active public-safe service summaries

Public provider responses do not include:
- national ID files
- private documents
- tax/bank/commercial register files
- raw storage paths
- admin notes
- internal contract terms
- unapproved providers
- suspended providers

## Tests Added

Added `UnifiedProviderFoundationSprint36Test` covering:
- provider type expansion
- legacy doctor public discovery stability
- generic future-provider onboarding
- branch map/address safety
- document visibility and audit
- needs-changes onboarding status
- hospital departments/doctors validation
- service catalog safety
- booking capability flags
- contract payment options
- admin-only contract management

## Still Not Implemented

Not implemented in Sprint 36:
- patient-facing radiology screens
- patient-facing gym/coach screens
- radiology orders
- gym memberships
- coach session booking
- home healthcare booking lifecycle
- hospital public page
- provider mobile app
- production launch readiness
- 10M user readiness

