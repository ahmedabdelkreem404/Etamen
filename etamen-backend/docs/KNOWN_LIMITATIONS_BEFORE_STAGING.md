# Known Limitations Before Staging

This document lists what remains blocked before any staging pilot or external user access.

## Deployment and Infrastructure

- No staging deployment has been accepted.
- Hostinger/SSH was not used in Sprint 61.
- `etamen.inolty.com` is not accepted.
- Production is not approved.
- App-store release is not approved.
- No server backup/restore validation has passed.
- No disaster recovery process has been tested.
- No load testing has been completed.

## Payments and Notifications

- No live Paymob acceptance.
- No live refund gateway integration.
- Refund foundation is manual/admin-confirmed only.
- No live FCM acceptance.

## Legal and Operations

- SOPs are operational drafts, not legal documents.
- Legal privacy policy approval is not complete.
- Refund/support policy approval is not complete.
- No external users should be invited.
- No real customer data should be entered.

## Product Scope

- Pharmacy/lab remain conservative MVP/smoke in local demo where full patient-facing payment regression was not the current sprint focus.
- Full provider portal is not complete.
- Platform Admin Operations Center is an MVP and does not replace Filament.
- Support/refund/dispute foundation does not include live settlement workflows.

## Security Scope

- Local security sweeps passed for checked endpoints.
- Staging hardening is still unverified.
- Production secrets/configuration have not been reviewed in this sprint.
