# Sprint 51 - Local Provider Operations MVP

Sprint 51 adds a local-only provider operations layer on top of the Sprint 50 workspace foundation.

This is not a full provider portal and does not replace Filament. It gives the Flutter app real, permission-guarded operational pages for local demo and future pilot preparation.

## Sprint 52 QA Completion

Sprint 52 completed the emulator QA gate that was still open after Sprint 51.

Result:

- doctor, hospital, radiology, pharmacy, lab, gym, coach, and limited staff provider workspaces were tested on the Android emulator.
- quick action routing was polished for coach packages.
- workspace switching was fixed in Flutter so provider dashboards open reliably after selection.
- provider operation API privacy sweep passed with no raw proof/result/prescription paths, payment configs, private provider documents, or cross-provider data leaks.
- final Sprint 52 decision: `LOCAL_PROVIDER_OPERATIONS_MVP_ACCEPTED`.

Details:

```text
etamen-backend/docs/SPRINT52_PROVIDER_OPERATIONS_QA_COMPLETION.md
```

## Scope

Implemented workspace-scoped backend endpoints under:

```text
/api/v1/provider/workspace/{provider}/...
```

All endpoints require:

- authenticated user
- active staff membership on the same provider
- provider type match
- specific provider permission

## Provider Types Covered

- doctor
- hospital
- radiology
- pharmacy
- lab
- gym
- fitness coach
- nutrition coach

## Backend Endpoints Added

Doctor:

- `GET /provider/workspace/{provider}/doctor/appointments`
- `GET /provider/workspace/{provider}/doctor/appointments/{appointment}`
- `POST /provider/workspace/{provider}/doctor/appointments/{appointment}/confirm`
- `POST /provider/workspace/{provider}/doctor/appointments/{appointment}/complete`
- `POST /provider/workspace/{provider}/doctor/appointments/{appointment}/cancel`

Hospital:

- `GET /provider/workspace/{provider}/hospital/appointments`
- `GET /provider/workspace/{provider}/hospital/departments`
- `GET /provider/workspace/{provider}/hospital/doctors`

Radiology:

- `GET /provider/workspace/{provider}/radiology/orders`
- `GET /provider/workspace/{provider}/radiology/orders/{order}`
- status actions: `accept`, `reject`, `start`, `result-ready`, `complete`

Pharmacy:

- `GET /provider/workspace/{provider}/pharmacy/orders`
- `GET /provider/workspace/{provider}/pharmacy/orders/{order}`
- `GET /provider/workspace/{provider}/pharmacy/products`

Lab:

- `GET /provider/workspace/{provider}/lab/orders`
- `GET /provider/workspace/{provider}/lab/orders/{order}`
- `GET /provider/workspace/{provider}/lab/catalog`

Gym:

- `GET /provider/workspace/{provider}/gym/bookings`
- `GET /provider/workspace/{provider}/gym/bookings/{booking}`
- status actions: `confirm`, `activate`, `complete`, `cancel`
- `GET /provider/workspace/{provider}/gym/plans`
- `GET /provider/workspace/{provider}/gym/classes`

Coach:

- `GET /provider/workspace/{provider}/coach/bookings`
- `GET /provider/workspace/{provider}/coach/bookings/{booking}`
- status actions: `confirm`, `start`, `complete`, `cancel`
- `GET /provider/workspace/{provider}/coach/availability`
- `GET /provider/workspace/{provider}/coach/session-types`
- `GET /provider/workspace/{provider}/coach/packages`

## Permission Guards

Examples:

- doctor appointment list/show requires `view_appointments`
- doctor appointment status actions require `manage_appointments`
- hospital appointment list requires `view_bookings`
- hospital departments requires `manage_departments`
- hospital doctors requires `manage_hospital_doctors`
- radiology list/show requires `view_radiology_orders`
- radiology status actions require `manage_radiology_orders`
- pharmacy orders require `view_pharmacy_orders`
- pharmacy products require `manage_pharmacy_products`
- lab orders require `view_lab_orders`
- lab catalog requires `manage_lab_catalog`
- gym bookings require `view_gym_bookings`
- gym status actions require `manage_gym_bookings`
- coach bookings require `view_coach_bookings`
- coach status actions require `manage_coach_bookings`

## Response Safety

Provider operation responses intentionally omit:

- raw proof file paths
- raw result file paths
- private provider documents
- national ID documents
- tax/commercial/bank documents
- payment provider config and secrets
- internal contract terms
- admin-only notes not meant for providers

Patient summaries are limited to safe identifiers and name.

## Actions Implemented vs Read Only

Writable status actions:

- doctor appointments
- radiology orders
- gym bookings
- coach bookings

Read-only in this MVP:

- hospital departments and doctors
- pharmacy orders and products
- lab orders and catalog

Pharmacy and lab actions remain conservative because Sprint 49 only smoke-tested those patient flows.

## Tests

Added:

- `tests/Feature/ProviderOperationsMvpSprint51Test.php`

Coverage includes:

- doctor owner list/confirm
- patient forbidden from provider operations
- wrong provider staff forbidden
- limited staff can view but cannot manage
- hospital appointments/departments/doctors
- radiology list and manage guard
- pharmacy/lab/gym/coach read endpoints
- no obvious private path/config leakage in provider operation responses

Latest local verification:

- `php artisan test tests/Feature/PilotDemoDataTest.php tests/Feature/WorkspaceProviderDashboardSprint50Test.php tests/Feature/ProviderOperationsMvpSprint51Test.php`: PASS, 12 tests / 211 assertions.
- Earlier full backend suite during Sprint 51 implementation: PASS, 254 tests / 2092 assertions.
- Final `git diff --check` must still be run before handoff.

## Local QA Notes

Local backend was reset with:

```text
php artisan migrate:fresh --seed
php artisan db:seed --class=PilotDemoSeeder
php artisan etamen:ensure-payment-methods --staging
```

The local server on port 8000 was restarted after a hung `php artisan serve` process caused temporary emulator login/API timeouts. After restart, `/api/v1/system/health` returned 200.

Emulator QA verified:

- doctor owner login
- workspace switcher
- doctor provider dashboard
- doctor appointments quick action
- doctor appointment details page

The full per-provider emulator screenshot set for hospital/radiology/pharmacy/lab/gym/coach was not completed in this pass. Backend feature tests cover these provider endpoint contracts and permission guards.

## Remaining Work

- Full provider portal UX
- Full per-provider emulator screenshot run for every owner workspace
- Provider file upload UI for radiology/lab results inside Flutter
- Pharmacy/lab full local paid regression
- Staff invitation flow for non-existing users
- Provider analytics and reports
- Staging deployment and real-phone provider dashboard QA
