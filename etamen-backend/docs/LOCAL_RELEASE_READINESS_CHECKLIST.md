# Local Release Readiness Checklist

Sprint 60 locks the current Etamen state as a local-only supervised pilot preparation baseline. This checklist is not a staging, production, public launch, app-store, or external-user approval.

## Locally Accepted

- Authentication, session restore, account page, and logout.
- Patient doctor booking with manual payment proof, platform admin review, and confirmed appointment refresh.
- Hospital discovery and hospital-context doctor booking reaching payment while preserving context.
- Radiology catalog, order creation, manual proof, admin accept, safe result metadata, and protected download flow.
- Gym booking with manual proof and admin accept.
- Coach booking with manual proof and admin accept.
- Provider workspace switcher, provider dashboard, and provider operations MVP.
- Limited staff permission guard and wrong-provider blocking.
- Platform Admin Operations Center for dashboard, payment reviews, provider approvals, support tickets, refunds, disputes, and audit log.
- Patient/provider support ticket foundation.
- Local security/privacy sweeps for patient, provider, and admin responses.

## Local Guardrails

- Flutter QA login buttons are allowed only when `ETAMEN_ENV=local`.
- Missing `ETAMEN_ENV`, `staging`, and `production` builds must not show QA login buttons.
- Short QA credentials such as `a@b.co`, `p@b.co`, and `d@b.co` are local/testing seed conveniences only.
- No local convenience may bypass normal authentication.
- Backend admin operations remain guarded by platform admin authorization.

## Not Accepted

- Hostinger deployment.
- `etamen.inolty.com` staging readiness.
- Production readiness.
- Public launch.
- App-store release.
- External users.
- Live Paymob or live payment gateway settlement.
- Live FCM.
- Legal privacy/refund/support policy approval.
- Real customer data.
- Load testing.
- Server backup/restore validation.
- Disaster recovery.

## Required Before Any Future Staging Gate

- Restore safe server access.
- Back up database and current deployed code.
- Deploy latest `main`.
- Run safe migrations and staging demo seed.
- Fix staging readiness without debug traces.
- Rebuild staging APK.
- Repeat real-phone proof/provider/admin QA against staging.
