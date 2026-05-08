# Sprint 37 Radiology Foundation

Date: 2026-05-08

Sprint 37 implements the first safe radiology backend/admin slice after the unified provider platform foundation. This is not a patient-facing module and does not change Flutter.

## Implemented

- Radiology scan category migration, model, seeder, API resource, admin API, and Filament resource.
- Radiology scan catalog migration, model, service layer, request validation, API resource, provider/admin APIs, policy, and Filament resource.
- Radiology preparation instruction migration, model, API resource, admin API, policy, and Filament resource.
- Safe public read-only catalog endpoints for future discovery, filtered to active scans and approved active radiology providers.
- Provider authorization rules so only the owning radiology provider can manage its own scans.
- Admin authorization rules so platform admins can manage categories/scans/instructions.
- Demo radiology provider data in `PilotDemoSeeder` for local/staging screenshots and QA only.
- Feature tests covering taxonomy, provider ownership, patient denial, admin management, branch validation, public visibility, disclaimer text, and private path safety.

## What Stayed Internal

- Radiology is not exposed in the Flutter patient app.
- Radiology does not have a patient order flow.
- Radiology does not have payment, proof upload, admin payment review, or result delivery.
- Radiology public catalog endpoints are read-only and are not wired into patient navigation.

## Security And Privacy

- No private provider documents are exposed.
- No raw storage paths are exposed.
- Patient users cannot create/update scans.
- Non-radiology providers cannot create scans.
- Suspended providers cannot manage scans.
- Unapproved radiology providers are hidden from public-safe listings.
- Preparation text avoids diagnosis/treatment advice and carries a general-instructions disclaimer.

## Demo Data

`PilotDemoSeeder` now adds local/staging-only demo radiology data:

- Demo radiology center in Cairo/Nasr City style address.
- Approved radiology provider profile and branch.
- Active scans across several seeded categories.
- Safe general preparation instructions.

This data must not be treated as production catalog data.

## Remaining Work

- Patient-facing radiology discovery UX.
- Radiology order lifecycle.
- Radiology booking/slot lifecycle if separate from appointments.
- Payment integration and refund/dispute handling for radiology.
- Result/report file upload and private patient download.
- Provider-side operational UI beyond admin APIs.
- Medical/legal review of preparation content.
- Load testing and search indexing.
