# Sprint 53 - Local Real Android Phone Gate

Date: 2026-05-10

Scope: local real-device QA only. No Hostinger, no staging, no SSH, no deployment.

## LAN Backend

- LAN API base used by APK: `http://192.168.1.5:8000/api/v1`
- Laravel command: `php artisan serve --host=0.0.0.0 --port=8000`
- Host health check: PASS
- LAN health check from host: PASS
- Phone browser health check: PASS
- Evidence: `I:\Etamen\.tmp\sprint53-real-phone-gate\connectivity\01-phone-health-browser.png`

## Device

- Device: Infinix X657C
- Android: 10
- SDK: 29
- Device id: `059833713C103883`
- Install method: ADB
- Note: `android-arm64` APK built, but install failed on this device with `INSTALL_FAILED_NO_MATCHING_ABIS`; the installed phone APK was rebuilt for `android-arm`.

## Patient Flow Results

- Auth/login/home/services/account: PASS on real phone.
- Logout/session restore: PASS after confirming the logout dialog. Reopen returned to login with `Unauthenticated.`
- Doctor booking/manual proof/admin accept: PASS.
- Radiology order/manual proof/admin accept/result metadata/download action: PASS.
- Gym booking/manual proof/admin accept: PASS.
- Coach booking/manual proof/admin accept: PASS.

## Provider Workspace Results

- Doctor owner dashboard and appointments page: PASS.
- Hospital owner dashboard and hospital appointments page: PASS.
- Radiology owner dashboard and orders page: PASS.
- Gym owner dashboard and bookings page: PASS.
- Coach owner dashboard and bookings page: PASS.
- Limited staff dashboard/list: PASS.
- Limited staff forced manage action: backend returned 403 as expected.

## Security Sweep

Saved result:

```text
I:\Etamen\.tmp\sprint53-real-phone-gate\security-sweep.json
```

Checked patient and provider responses used in this sprint. No forbidden leak terms were found:

- private storage paths
- raw proof/result paths
- raw file URLs
- payment config
- Paymob secrets
- provider private documents
- national ID/tax/commercial/bank document terms
- internal contracts
- admin-only notes

## Evidence

Screenshots root:

```text
I:\Etamen\.tmp\sprint53-real-phone-gate\
```

Local APK:

```text
I:\Etamen\.tmp\etamen-local-real-phone.apk
C:\Users\Ahmed Abdelkareem\OneDrive\Desktop\Etamen_Android_Website_Ready\etamen-local-real-phone.apk
```

## Tests And Build

- Backend `php artisan test`: PASS, 254 tests / 2092 assertions.
- Backend `git diff --check`: PASS.
- Flutter `flutter analyze`: PASS.
- Flutter `flutter test`: PASS, 187 tests.
- Flutter local-phone `android-arm` debug APK build: PASS.
- Flutter `git diff --check`: PASS.

## Decision

```text
LOCAL_REAL_PHONE_GATE_ACCEPTED
```

This is not staging readiness, public launch readiness, production readiness, or app-store readiness.
