# Local Demo Scope Lock

Date: 2026-05-10

## Purpose

This document locks what is accepted for the local Etamen demo after Sprint 49.

It prevents the local emulator proof from being mistaken for staging, production, or real-phone approval.

## Locally Accepted

The following flows are accepted locally on Android emulator against:

```text
http://10.0.2.2:8000/api/v1
```

Accepted:

- authentication/session
- doctor booking/payment proof/admin accept
- hospital discovery/context booking to payment
- radiology order/payment proof/admin accept/result metadata/download action
- gym booking/payment proof/admin accept
- coach booking/payment proof/admin accept

## Smoke Only

The following were checked as smoke only in Sprint 49:

- pharmacy list/products entry
- lab list/catalog entry

Full pharmacy/lab paid E2E was not re-run in Sprint 49.

## Not Approved

The local acceptance does not approve:

- staging
- Hostinger
- `etamen.inolty.com`
- real Android phone readiness
- production launch
- public launch
- live Paymob
- live FCM
- legal/refund/support SOPs
- load testing
- app store release

## Final Local Decision

```text
LOCAL_SUPERAPP_REGRESSION_ACCEPTED
```

## Exact Next Gate

Fix staging access/readiness/data, deploy the known-good backend safely, build a staging APK, then test on a real Android phone:

- login
- doctor booking
- real payment proof upload
- admin review of the same payment
- Flutter state refresh
- logout/session restore

## Sprint 50 Local Addition

Accepted locally after Sprint 50:

- unified workspace endpoint
- Flutter workspace switcher
- provider dashboard shell for doctor, hospital, radiology, gym, and coach accounts
- platform admin shell
- provider staff permission foundation
- limited staff dashboard with backend-filtered permissions

Still not approved:

- full provider portal operations
- provider invitation flow
- staging provider dashboard
- public launch

## Sprint 51 Local Provider Operations Update

Sprint 51 expands the local provider dashboard shell into limited operational pages.

Implemented and covered by backend/Flutter tests:

- doctor appointment operations
- hospital appointments/departments/doctors read views
- radiology order operations
- pharmacy orders/products read views
- lab orders/catalog read views
- gym bookings/plans/classes operations/read views
- coach bookings/availability/session types/packages operations/read views
- backend permission enforcement for provider operations

Emulator verification completed for:

- doctor owner workspace switcher
- doctor provider dashboard
- doctor appointments list
- doctor appointment details

Full per-provider emulator screenshots for hospital/radiology/pharmacy/lab/gym/coach are still pending, so Sprint 51 is not locked as a fully accepted local provider-ops gate yet.

Still not approved:

- full provider portal completeness
- provider-side result upload UI for all verticals
- staging readiness
- real Android phone readiness
- public or production launch

## Sprint 52 Local Provider Operations QA Lock

Sprint 52 completes the provider operations MVP gate locally.

Accepted locally after emulator QA and tests:

- doctor provider operations
- hospital provider operations
- radiology provider operations
- pharmacy provider read-only MVP pages
- lab provider read-only MVP pages
- gym provider operations
- coach provider operations
- limited staff permission filtering and blocked wrong-provider API access
- quick action routing, including coach packages
- provider operation privacy sweep

Evidence:

```text
I:\Etamen\.tmp\sprint52-provider-operations-qa\
```

APK:

```text
I:\Etamen\.tmp\etamen-local-provider-operations-qa.apk
C:\Users\Ahmed Abdelkareem\OneDrive\Desktop\Etamen_Android_Website_Ready\etamen-local-provider-operations-qa.apk
```

Decision:

```text
LOCAL_PROVIDER_OPERATIONS_MVP_ACCEPTED
```

Still not approved:

- full provider portal
- staging
- Hostinger
- real phone provider operations QA
- public or production launch

## Sprint 53 Real Phone Local Gate

Accepted locally on a real Android phone:

- patient auth/session/logout
- doctor booking/payment proof/admin accept
- radiology order/payment proof/admin accept/result metadata/download success state
- gym booking/payment proof/admin accept
- coach booking/payment proof/admin accept
- provider workspace dashboards and operation pages for doctor, hospital, radiology, gym, and coach
- limited staff restricted access

Evidence:

```text
I:\Etamen\.tmp\sprint53-real-phone-gate\
```

APK:

```text
I:\Etamen\.tmp\etamen-local-real-phone.apk
C:\Users\Ahmed Abdelkareem\OneDrive\Desktop\Etamen_Android_Website_Ready\etamen-local-real-phone.apk
```

Still not approved:

- staging
- Hostinger
- production/public launch
- app store release
- live Paymob
- live FCM
- legal/support/refund SOP approval
- load testing

## Sprint 54 Staging Attempt

Sprint 54 did not change the local demo lock.

Staging attempt result:

```text
STAGING_ACCESS_BLOCKED
```

Reason:

- server access remains blocked.
- latest local accepted behavior could not be deployed to staging.
- staging readiness is still unhealthy.
- staging data/payment/provider workspace routes are incomplete.

The local demo acceptance from Sprints 49-53 remains valid only for local emulator/LAN real-phone testing. It does not approve Hostinger, staging, production, public launch, app-store release, or external users.

## Sprint 55 Access-First Staging Recovery

Sprint 55 ended at:

```text
STAGING_ACCESS_BLOCKED
```

No staging deployment occurred. No staging APK was accepted. No real-phone staging QA was run.

The local demo lock remains unchanged:

- local emulator/LAN phone flows are accepted locally.
- Hostinger/staging is not accepted.
- external users must not be invited.

## Sprint 58 Local Admin Operations Center

Sprint 58 added local admin operations APIs and Flutter pages for:

- admin operations dashboard
- payment review queue
- provider approval queue
- support tickets
- refund requests
- disputes
- audit log
- patient/provider support basics

Automated backend and Flutter tests passed, and a local debug APK was built:

```text
I:\Etamen\.tmp\etamen-local-admin-operations.apk
```

Sprint 58 was implemented but was not accepted as a local demo gate yet. The emulator visual QA screenshot set was incomplete. The admin workspace switcher, dashboard, quick actions, and payment queue were captured, but the remaining admin/support/refund/dispute/non-admin-blocked screens needed a clean visual pass.

Current Sprint 58 decision:

```text
LOCAL_ADMIN_OPERATIONS_NOT_READY_DUE_BLOCKERS
```

Still not approved:

- staging
- Hostinger
- production/public launch
- app store release
- external users

## Sprint 59 Local Admin Operations Lock

Accepted locally after Sprint 59:

- platform admin operations dashboard
- admin payment review queue and details
- admin provider approval queue and details
- support ticket foundation for admin, patient, and provider users
- refund/dispute foundation screens and APIs
- audit log viewing
- non-admin blocking for patient/provider users

Evidence:

```text
I:\Etamen\.tmp\sprint59-local-admin-operations-qa\
```

APK:

```text
I:\Etamen\.tmp\etamen-local-admin-operations-qa.apk
C:\Users\Ahmed Abdelkareem\OneDrive\Desktop\Etamen_Android_Website_Ready\etamen-local-admin-operations-qa.apk
```

Decision:

```text
LOCAL_ADMIN_OPERATIONS_CENTER_ACCEPTED
```

Still not approved:

- staging
- Hostinger
- production/public launch
- app store release
- live Paymob
- external users
- final legal/support/refund SOPs

## Sprint 60 Local SOP Guardrails Lock

Sprint 60 does not expand product scope. It locks the local demo/pilot guardrails around the already accepted local flows.

Accepted locally:

- QA login buttons are visible only for `ETAMEN_ENV=local`.
- Missing/unsafe environment fallback does not expose QA buttons.
- Short QA credentials are local/testing seed conveniences only.
- Local release readiness checklists exist.
- Pilot operations SOP exists.
- Privacy/data handling SOP exists.
- Medical safety SOP exists.
- Admin and provider runbooks exist.
- Patient support/refund/dispute guide exists.

Still not approved:

- staging
- Hostinger
- production/public launch
- app store release
- live Paymob
- live FCM
- external users
- real customer data
- server backup/restore validation
- disaster recovery

## Sprint 61 Final Local Demo Lock

Sprint 61 packages the accepted local system for internal demos only.

Accepted local artifacts:

- final regression matrix
- demo accounts docs
- local demo walkthrough guide
- internal handoff docs
- known limitations before staging
- final APK
- final screenshot set
- final security sweep

Decision:

```text
LOCAL_FINAL_DEMO_PACKAGE_ACCEPTED
```

Hard lock:

- no staging claim
- no production claim
- no public launch claim
- no app-store claim
- no external user invitations
- no live payment/refund claims

## Sprint 62 Internal Demo Rehearsal Lock

Sprint 62 makes the internal local demo easier to present without changing launch scope.

Accepted local rehearsal artifacts:

- Arabic demo script
- demo timeline
- stakeholder FAQ
- product one-pager
- internal demo QA checklist
- local rehearsal APK
- local rehearsal screenshots

Decision:

```text
LOCAL_INTERNAL_DEMO_REHEARSAL_ACCEPTED
```

This still means:

- not staging
- not production
- not public launch
- not app-store ready
- not approved for external users
- not approved for live payments or live refunds

Next real gate:

- server access plus backup-first staging deploy and readiness/data verification.

## Sprint 63 Client/Investor Demo Polish Lock

Sprint 63 improves how the local demo is presented to clients or investors without changing launch approval.

Accepted local polish artifacts:

- client/investor narrative
- demo talk track
- objection handling
- module map
- demo risk register
- fallback plan
- local client demo APK
- local client demo screenshot pack

Decision:

```text
LOCAL_CLIENT_DEMO_POLISH_ACCEPTED
```

This still means:

- local demo only
- not staging ready
- not production ready
- not public launch ready
- not app-store ready
- no external users
- no live payments or live refunds

Next real gate:

- server access, backup, staging deploy, readiness/data checks, then staging real-phone QA.

## Sprint 64 Local Demo Freeze Lock

Sprint 64 selected Path B because staging/server access was not safely confirmed.

Accepted local freeze artifacts:

- local demo freeze docs
- no-external-users-until-staging docs
- local freeze APK
- final local tests/build verification

Decision:

```text
LOCAL_DEMO_FREEZE_ACCEPTED
```

This still means:

- local demo only
- no staging
- no production
- no public launch
- no app-store
- no external users
- no live payments
- no real medical data

Next real gate:

- restore server access safely, back up staging, deploy main, verify readiness/data/security, then run staging real-phone QA.

## Sprint 66 Local Pharmacy/Lab Hardening

Sprint 66 keeps the scope local-only and improves the weakest patient modules.

Accepted locally after Sprint 66:

- pharmacy list/details/catalog/order creation.
- pharmacy prescription metadata and manual payment proof flow.
- pharmacy order details with friendly payment/admin-review status.
- pharmacy cancel-before-payment only.
- lab list/details/catalog/order creation.
- lab order details with friendly payment/admin-review status.
- lab result metadata and protected download state.
- lab cancel-before-payment only.
- provider pharmacy/lab order views remain scoped to own provider.
- no raw prescription or lab result paths.
- no diagnosis or medical interpretation.
- decision: `LOCAL_PHARMACY_LAB_PATIENT_FLOWS_ACCEPTED`.
- evidence: backend `265 tests / 2168 assertions`, Flutter `196 tests`, APK `I:/Etamen/.tmp/etamen-local-pharmacy-lab-hardening.apk`, screenshots `I:/Etamen/.tmp/sprint66-local-pharmacy-lab-hardening/`.

Still not approved:

- staging readiness.
- production readiness.
- public launch.
- app-store release.
- external users.
- live payments or live refunds.

## Sprint 69 Local Pharmacy/Lab Catalog Polish Lock

Sprint 69 is accepted for local demo scope only.

Decision:

```text
LOCAL_PHARMACY_LAB_CATALOG_POLISH_ACCEPTED
```

Accepted locally:

- pharmacy product catalog search/filter/sort.
- lab tests/packages catalog search/filter/sort.
- provider pharmacy/lab catalog search/filter/sort for own-provider catalog only.
- selected-items summaries with backend-owned final total copy.
- broader pharmacy/lab catalog seed variety.
- inactive/private public catalog visibility rules.
- no raw prescription/result paths, no secrets, no payment config, no private provider docs, and no medical interpretation.

Evidence:

- backend `273 tests / 2392 assertions`.
- Flutter `202 tests`.
- screenshots `I:/Etamen/.tmp/sprint69-local-pharmacy-lab-catalog-polish/`.
- APK `I:/Etamen/.tmp/etamen-local-pharmacy-lab-catalog-polish.apk`.

Still not approved:

- production readiness.
- public launch.
- app-store release.
- external users.
- live payments or live refunds.
- medical interpretation.

## Sprint 68 Local Pharmacy/Lab History Lock

Sprint 68 is accepted for local demo scope only.

Decision:

```text
LOCAL_PHARMACY_LAB_HISTORY_POLISH_ACCEPTED
```

Accepted locally:

- pharmacy/lab patient order-history filters and timeline/status UX.
- provider pharmacy/lab history filters and safe action panels.
- admin payment review context labels for pharmacy/lab.
- limited staff friendly no-permission state.
- local seed states for order-history demos.
- no raw prescription/result paths, no secrets, no payment config, and no medical interpretation.

Evidence:

- backend `269 tests / 2333 assertions`.
- Flutter `199 tests`.
- screenshots `I:/Etamen/.tmp/sprint68-local-pharmacy-lab-history-polish/`.
- APK `I:/Etamen/.tmp/etamen-local-pharmacy-lab-history-polish.apk`.

Still not approved:

- staging readiness.
- production readiness.
- public launch.
- app-store release.
- external users.
- live payments or live refunds.
- medical interpretation.

Next local product recommendation:

- continue local patient-flow polish around pharmacy/lab cart ergonomics, order history filters, and demo evidence.

## Sprint 65 Staging Access Gate Lock

Sprint 65 did not change the local demo scope. It only checked whether staging access was usable.

Decision:

```text
STAGING_ACCESS_STILL_BLOCKED
```

Result:

- SSH key access still failed.
- no server files were touched.
- no `.env` was read.
- no deploy, migration, seed, cache clear, Composer install, storage link, or staging APK build happened.
- public staging API remains incomplete: readiness 500, payment methods empty, hospitals/gyms/coaches 404, radiology scans empty.

This still means:

- local demo only
- not staging ready
- not production ready
- not public launch ready
- not app-store ready
- no external users
- no live payments or live refunds

Next real gate:

- fix Hostinger access, verify backup feasibility, then run backup-first staging recovery.

## Sprint 67 Local Pharmacy/Lab Provider Actions Lock

Sprint 67 is accepted for local demo scope only.

Decision:

```text
LOCAL_PHARMACY_LAB_PROVIDER_ACTIONS_ACCEPTED
```

Accepted locally:

- Sprint 66 docs corrected to `LOCAL_PHARMACY_LAB_PATIENT_FLOWS_ACCEPTED`.
- pharmacy provider lifecycle actions: accept, reject with reason, preparing, ready, out_for_delivery, complete.
- lab provider lifecycle actions: accept, reject with reason, sample_scheduled, sample_collected, processing, result_ready, complete.
- admin payment review regression for pharmacy/lab payment proof contexts.
- limited staff and wrong-provider blocks verified.
- no raw prescription/result paths, no secrets, no payment config, and no medical interpretation.

Evidence:

- backend `267 tests / 2269 assertions`.
- Flutter `197 tests`.
- screenshots `I:/Etamen/.tmp/sprint67-local-pharmacy-lab-provider-actions/`.
- APK `I:/Etamen/.tmp/etamen-local-pharmacy-lab-provider-actions.apk`.

Still not approved:

- production readiness.
- public launch.
- app-store release.
- external users.
- live payments or live refunds.
