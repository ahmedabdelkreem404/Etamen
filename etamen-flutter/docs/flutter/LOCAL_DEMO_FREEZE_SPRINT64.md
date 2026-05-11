# Sprint 64 Local Demo Freeze

Selected path:

```text
PATH B: LOCAL DEMO FREEZE
```

Staging/server access was not safely confirmed at sprint start, so the approved work is a local freeze only. No Hostinger, SSH, staging, deployment, or server file action happened.

## Accepted local decisions

```text
LOCAL_REAL_PHONE_GATE_ACCEPTED
LOCAL_ADMIN_OPERATIONS_CENTER_ACCEPTED
LOCAL_PILOT_OPERATIONS_SOP_ACCEPTED
LOCAL_FINAL_DEMO_PACKAGE_ACCEPTED
LOCAL_INTERNAL_DEMO_REHEARSAL_ACCEPTED
LOCAL_CLIENT_DEMO_POLISH_ACCEPTED
```

## APK paths

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

Sprint 64:

```text
I:/Etamen/.tmp/etamen-local-demo-freeze.apk
C:/Users/Ahmed Abdelkareem/OneDrive/Desktop/Etamen_Android_Website_Ready/etamen-local-demo-freeze.apk
```

## Screenshot roots

```text
I:/Etamen/.tmp/sprint61-local-final-demo/
I:/Etamen/.tmp/sprint62-local-demo-rehearsal/
I:/Etamen/.tmp/sprint63-local-client-demo-polish/
```

## Demo commands

Backend:

```powershell
cd I:/Etamen/etamen-backend
php artisan migrate:fresh --seed
php artisan db:seed --class=PilotDemoSeeder
php artisan etamen:ensure-payment-methods --staging
php artisan serve --host=0.0.0.0 --port=8000
```

APK install target:

```text
http://10.0.2.2:8000/api/v1
ETAMEN_ENV=local
```

## Approved locally

- patient demo
- provider workspace demo
- admin operations demo
- support/refund/dispute foundation
- local client/investor demo narrative
- local screenshot fallback

## Not approved

- staging
- production
- public launch
- app-store release
- external users
- live payments
- live refunds
- real medical data
- real providers

## Freeze rule

No demo should be described as launch-ready. Say: "Etamen is currently approved for internal local demos only. The next real gate is server access plus backup-first staging deployment."
