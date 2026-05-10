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

Sprint 55 also ended at:

```text
STAGING_ACCESS_BLOCKED
```

No staging deployment occurred. No staging APK was accepted. No real-phone staging QA was run.

The local demo lock remains unchanged:

- local emulator/LAN phone flows are accepted locally.
- Hostinger/staging is not accepted.
- external users must not be invited.
