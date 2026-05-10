# Sprint 58 - Local Admin Operations Center

Sprint 58 added a local-only Platform Admin operations layer. It does not approve staging, Hostinger, production, public launch, app-store release, or replacement of Filament.

## Backend Result

Implemented backend-owned admin operations APIs under:

```text
/api/v1/admin/operations/*
```

Covered areas:

- operations dashboard summary
- pending payment review queue
- provider approval queue
- support tickets
- refund requests
- disputes
- audit log

Authorization remains backend-owned. Non-admin users receive `403` from admin operations endpoints.

## APIs Added

Admin:

- `GET /api/v1/admin/operations/dashboard`
- `GET /api/v1/admin/operations/payments/pending`
- `GET /api/v1/admin/operations/payments/{payment}`
- `POST /api/v1/admin/operations/payments/{payment}/accept`
- `POST /api/v1/admin/operations/payments/{payment}/reject`
- `GET /api/v1/admin/operations/providers/pending`
- `GET /api/v1/admin/operations/providers/{provider}`
- `POST /api/v1/admin/operations/providers/{provider}/approve`
- `POST /api/v1/admin/operations/providers/{provider}/reject`
- `POST /api/v1/admin/operations/providers/{provider}/suspend`
- `GET /api/v1/admin/operations/support/tickets`
- `GET /api/v1/admin/operations/support/tickets/{ticket}`
- `POST /api/v1/admin/operations/support/tickets/{ticket}/reply`
- `POST /api/v1/admin/operations/support/tickets/{ticket}/internal-note`
- `POST /api/v1/admin/operations/support/tickets/{ticket}/assign`
- `POST /api/v1/admin/operations/support/tickets/{ticket}/close`
- `GET /api/v1/admin/operations/refunds`
- `GET /api/v1/admin/operations/refunds/{refund}`
- `POST /api/v1/admin/operations/refunds/{refund}/mark-under-review`
- `POST /api/v1/admin/operations/refunds/{refund}/approve`
- `POST /api/v1/admin/operations/refunds/{refund}/reject`
- `POST /api/v1/admin/operations/refunds/{refund}/mark-processed`
- `GET /api/v1/admin/operations/disputes`
- `GET /api/v1/admin/operations/disputes/{dispute}`
- `POST /api/v1/admin/operations/disputes/{dispute}/assign`
- `POST /api/v1/admin/operations/disputes/{dispute}/resolve`
- `POST /api/v1/admin/operations/disputes/{dispute}/close`
- `GET /api/v1/admin/operations/audit-log`

Patient/provider:

- `POST /api/v1/support/tickets`
- `GET /api/v1/support/tickets`
- `GET /api/v1/support/tickets/{ticket}`
- `POST /api/v1/support/tickets/{ticket}/messages`
- `POST /api/v1/refunds`
- `GET /api/v1/refunds`
- `GET /api/v1/refunds/{refund}`
- `POST /api/v1/disputes`
- `GET /api/v1/disputes`
- `GET /api/v1/disputes/{dispute}`

## Tables Added

- `support_tickets`
- `support_ticket_messages`
- `refund_requests`
- `disputes`

Existing payment/provider audit facilities are reused where available.

## Payment Review

The admin payment queue reuses existing manual payment review logic. Accept/reject actions update linked entities through existing payment rules for:

- doctor appointments
- radiology orders
- gym bookings
- coach bookings

Proof response payloads expose safe metadata only:

- proof exists
- safe filename
- upload date
- mime type
- file size

Raw proof paths are not exposed.

## Provider Approval

Provider approval queue supports doctor, hospital, radiology, pharmacy, lab, gym, fitness coach, and nutrition coach providers.

Responses show safe provider summaries and document checklist metadata only. Raw national ID, tax, commercial, bank, and contract file paths are not exposed.

## Support, Refunds, Disputes

Support tickets support patient/provider creation and replies. Admin-only internal notes are hidden from patient/provider responses.

Refund and dispute workflows are foundation-only:

- approved refund means platform decision, not money sent
- processed means manual/admin confirmation
- no live refund gateway was integrated

## Seed Data

`PilotDemoSeeder` now creates local demo data for:

- platform admin user
- three pending payment review records
- two pending provider approvals
- three support tickets
- two refund requests
- two disputes

Seed data is local demo only and idempotent-oriented.

## Tests

Backend Sprint 58 feature tests pass, including:

- non-admin blocked from admin dashboard
- admin dashboard access
- payment queue proof path hiding
- payment accept updates linked appointment
- payment reject requires reason
- provider document path hiding
- provider approval audit event
- support ticket scoping and internal note hiding
- refund/dispute admin actions
- audit log admin-only access

Full backend test result:

```text
php artisan test
261 passed (2132 assertions)
```

## Local QA

Local migration, seeding, backend tests, Flutter tests, and APK build passed. Emulator visual QA was partially completed after using a local-only short QA admin email (`a@b.co`) to work around ADB email text-entry issues.

Captured:

- admin login/home
- workspace switcher with Platform Admin workspace
- admin operations dashboard
- admin dashboard quick actions
- payment review queue

Screenshot root:

```text
I:\Etamen\.tmp\sprint58-local-admin-operations\
```

APK:

```text
I:\Etamen\.tmp\etamen-local-admin-operations.apk
C:\Users\Ahmed Abdelkareem\OneDrive\Desktop\Etamen_Android_Website_Ready\etamen-local-admin-operations.apk
```

## Security Result

Security sweep artifact:

```text
I:\Etamen\.tmp\sprint58-local-admin-operations\security-sweep.json
```

No raw proof paths, result paths, provider private docs, payment config, secrets, internal contracts, or patient medical private records are intentionally exposed by the Sprint 58 resources.

## Remaining Blockers

- Complete emulator visual QA screenshots for payment details, provider approvals/details, support tickets/details, refunds/details, disputes/details, audit log, patient support/refund/dispute, provider support, and non-admin blocked screens.
- Investigate the local emulator ANR/text-entry fragility observed during app data reset/login automation.
- Run a manual app walkthrough after ANR is resolved before marking Sprint 58 accepted.

## Decision

```text
LOCAL_ADMIN_OPERATIONS_NOT_READY_DUE_BLOCKERS
```
