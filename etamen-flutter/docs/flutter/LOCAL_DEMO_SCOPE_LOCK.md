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
