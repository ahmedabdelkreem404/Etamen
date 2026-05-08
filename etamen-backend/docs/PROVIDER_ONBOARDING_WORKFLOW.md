# Provider Onboarding Workflow

Date: 2026-05-08  
Sprint: 36 - Unified Provider Platform Foundation

## Statuses

Provider statuses:
- `draft`: provider record is not submitted yet.
- `pending_review`: owner submitted profile for admin review.
- `needs_changes`: admin reviewed and requested corrections.
- `approved`: provider is approved and can become publicly visible if its type is enabled.
- `rejected`: provider is rejected.
- `suspended`: provider was approved before but is now disabled.

## Owner Journey

1. Owner creates provider account/profile.
2. Owner chooses provider type.
3. Owner fills safe provider details.
4. Owner adds branch/location data.
5. Owner uploads verification documents.
6. Provider is submitted as `pending_review`.
7. Admin reviews profile and documents.
8. Admin approves, rejects, requests changes, or suspends.

## Admin Journey

Admin can:
- view providers.
- filter/manage provider foundation data in Filament.
- approve provider.
- reject provider with reason.
- request changes with reason.
- suspend/reactivate provider.
- approve/reject provider documents.
- manage service catalog foundation.
- manage booking settings.
- manage provider contract foundation.

API admin actions:
- `POST /api/v1/admin/providers/{provider}/approve`
- `POST /api/v1/admin/providers/{provider}/reject`
- `POST /api/v1/admin/providers/{provider}/request-changes`
- `POST /api/v1/admin/providers/{provider}/suspend`
- `POST /api/v1/admin/providers/{provider}/reactivate`
- `POST /api/v1/admin/provider-documents/{document}/approve`
- `POST /api/v1/admin/provider-documents/{document}/reject`
- `POST /api/v1/admin/providers/{provider}/contracts`

## Public Visibility

Only approved and active providers can appear publicly.

Public discovery is still enabled only for current MVP verticals:
- doctors
- pharmacies
- labs

Future provider types are valid in backend/admin but not exposed to patients yet.

## Security Rules

Provider owners cannot:
- force `approved` status.
- force `is_active`.
- set rating/avatar visual trust fields.
- set contract or commission fields.
- set backend-owned service price through provider API.
- assign services to another provider.

Patients cannot:
- approve providers.
- manage provider services.
- manage provider contracts.
- see private provider documents.

## Remaining Work

Future sprints should add:
- provider-facing document checklist UX.
- admin review screens with clearer operational workflow.
- public discovery only when each vertical has complete API/UI/order lifecycle.
- provider app or provider portal if required.

