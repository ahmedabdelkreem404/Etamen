# Local Release Readiness Checklist

This checklist describes the local Flutter app gate only. It does not approve staging, production, public launch, app-store release, or external users.

## Locally Accepted Screens and Flows

- Login, session restore, Account, and logout.
- Services patient flows for doctor, hospital context, radiology, gym, and coach.
- Manual proof upload and backend/admin state refresh for accepted local flows.
- Provider workspace switcher and provider dashboard.
- Provider operations pages for doctor, hospital, radiology, pharmacy/lab MVP, gym, and coach.
- Platform Admin workspace for dashboard, payments, providers, support, refunds, disputes, and audit log.
- Patient support ticket, refund request, and dispute foundation.
- Provider support shortcut.
- Non-admin users do not see Platform Admin workspace.

## Environment Guardrails

- QA login buttons are shown only when `ETAMEN_ENV=local`.
- QA login buttons are hidden for `staging`, `production`, and missing/unsafe environment fallback.
- QA login uses the normal login flow and does not bypass authentication.
- Local build command must pass `--dart-define=ETAMEN_ENV=local` when QA buttons are needed.

## Not Accepted

- Hostinger.
- `etamen.inolty.com`.
- Staging.
- Production.
- Public launch.
- App-store release.
- External users.
- Live Paymob.
- Live FCM.
- Legal/privacy/refund policy approval.
- Real customer data.
- Load testing.
- Server backup/restore or disaster recovery.
