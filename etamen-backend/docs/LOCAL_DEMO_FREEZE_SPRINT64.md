# Sprint 64 Local Demo Freeze

Selected path:

```text
PATH B: LOCAL DEMO FREEZE
```

Reason: safe staging/Hostinger/SSH access was not confirmed at sprint start. No server, hosting, SSH, staging, deployment, migration, or seed action was performed.

## Current accepted local decisions

```text
LOCAL_REAL_PHONE_GATE_ACCEPTED
LOCAL_ADMIN_OPERATIONS_CENTER_ACCEPTED
LOCAL_PILOT_OPERATIONS_SOP_ACCEPTED
LOCAL_FINAL_DEMO_PACKAGE_ACCEPTED
LOCAL_INTERNAL_DEMO_REHEARSAL_ACCEPTED
LOCAL_CLIENT_DEMO_POLISH_ACCEPTED
```

## Frozen local APK artifacts

Sprint 61:

```text
I:/Etamen/.tmp/etamen-local-final-demo.apk
C:/Users/Ahmed Abdelkareem/OneDrive/Desktop/Etamen_Android_Website_Ready/etamen-local-final-demo.apk
```

Sprint 62:

```text
I:/Etamen/.tmp/etamen-local-internal-demo-rehearsal.apk
C:/Users/Ahmed Abdelkareem/OneDrive/Desktop/Etamen_Android_Website_Ready/etamen-local-internal-demo-rehearsal.apk
```

Sprint 63:

```text
I:/Etamen/.tmp/etamen-local-client-demo-polish.apk
C:/Users/Ahmed Abdelkareem/OneDrive/Desktop/Etamen_Android_Website_Ready/etamen-local-client-demo-polish.apk
```

Sprint 64 freeze APK:

```text
I:/Etamen/.tmp/etamen-local-demo-freeze.apk
C:/Users/Ahmed Abdelkareem/OneDrive/Desktop/Etamen_Android_Website_Ready/etamen-local-demo-freeze.apk
```

## Screenshot roots

```text
I:/Etamen/.tmp/sprint53-real-phone-gate/
I:/Etamen/.tmp/sprint59-local-admin-operations-qa/
I:/Etamen/.tmp/sprint61-local-final-demo/
I:/Etamen/.tmp/sprint62-local-demo-rehearsal/
I:/Etamen/.tmp/sprint63-local-client-demo-polish/
```

## Docs map

- `LOCAL_FINAL_REGRESSION_MATRIX.md`
- `LOCAL_DEMO_ACCOUNTS.md`
- `LOCAL_INTERNAL_HANDOFF.md`
- `KNOWN_LIMITATIONS_BEFORE_STAGING.md`
- `LOCAL_RELEASE_READINESS_CHECKLIST.md`
- `PILOT_OPERATIONS_SOP.md`
- `PRIVACY_DATA_HANDLING_SOP.md`
- `MEDICAL_SAFETY_SOP.md`
- `ADMIN_OPERATIONS_RUNBOOK.md`
- `PROVIDER_OPERATIONS_RUNBOOK.md`
- `CLIENT_INVESTOR_DEMO_NARRATIVE_AR.md`
- `OBJECTION_HANDLING_AR.md`
- `PRODUCT_MODULE_MAP_AR.md`
- `DEMO_RISK_REGISTER.md`
- `NO_EXTERNAL_USERS_UNTIL_STAGING.md`

## Local demo commands

Backend:

```powershell
cd I:/Etamen/etamen-backend
php artisan migrate:fresh --seed
php artisan db:seed --class=PilotDemoSeeder
php artisan etamen:ensure-payment-methods --staging
php artisan serve --host=0.0.0.0 --port=8000
```

Flutter local APK build:

```powershell
cd I:/Etamen/etamen-flutter
flutter build apk --debug --target-platform android-x64 --dart-define=ETAMEN_API_BASE_URL=http://10.0.2.2:8000/api/v1 --dart-define=ETAMEN_ENV=local
```

## Approved locally

- patient demo flows
- provider workspaces and operations MVP
- Platform Admin Operations Center
- support/refund/dispute foundation
- local-only QA accounts and buttons
- internal/client local demo package
- local demo screenshots and fallback docs

## Not approved

- Hostinger
- staging
- production
- public launch
- app-store release
- external users
- live Paymob
- live refund gateway
- real patient/provider data
- server backup/restore validation
- load testing
- final legal/privacy/payment/support approval

## Freeze rule

Until staging is safely recovered and accepted, Etamen must be presented only as an internal/local demo. Do not invite external users or imply production readiness.
