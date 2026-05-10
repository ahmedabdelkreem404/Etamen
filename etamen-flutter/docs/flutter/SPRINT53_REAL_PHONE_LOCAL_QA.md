# Sprint 53 - Real Phone Local QA

Date: 2026-05-10

This sprint tested the local APK on a real Android phone against a LAN backend URL only.

## Device And Install

- Phone: Infinix X657C
- Android: 10
- SDK: 29
- LAN API: `http://192.168.1.5:8000/api/v1`
- Install: ADB
- `android-arm64` build passed but could not install on this device because the phone rejected the ABI.
- Installed build: `android-arm`

APK:

```text
I:\Etamen\.tmp\etamen-local-real-phone.apk
C:\Users\Ahmed Abdelkareem\OneDrive\Desktop\Etamen_Android_Website_Ready\etamen-local-real-phone.apk
```

## Patient QA

- Auth/session/logout: PASS.
- Doctor booking -> Vodafone Cash -> real phone proof image -> admin accept -> confirmed appointment: PASS.
- Radiology order -> proof upload -> admin accept -> visible result metadata -> download success snackbar: PASS.
- Gym membership booking -> proof upload -> admin accept -> confirmed booking: PASS.
- Coach booking -> proof upload -> admin accept -> confirmed booking: PASS.

## Provider QA

- Doctor provider dashboard and appointments page: PASS.
- Hospital provider dashboard and appointments page: PASS.
- Radiology provider dashboard and orders page: PASS.
- Gym provider dashboard and bookings page: PASS.
- Coach provider dashboard and bookings page: PASS.
- Limited staff dashboard: PASS.
- Limited staff manage attempt: backend 403, UI exposes only limited actions.

## Screenshot Evidence

```text
I:\Etamen\.tmp\sprint53-real-phone-gate\
```

Important subfolders:

- `auth`
- `doctor`
- `radiology`
- `gym`
- `coach`
- `provider`

## Security

Security sweep saved at:

```text
I:\Etamen\.tmp\sprint53-real-phone-gate\security-sweep.json
```

No private paths, raw proof/result paths, payment config, Paymob secrets, or provider private document terms were found in checked responses.

## Notes

- Some ADB text entry on the Infinix keyboard required switching the keyboard to English before entering seeded provider emails.
- The installed APK is local-phone only and points to the developer laptop LAN IP.

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

This does not approve staging, public launch, production, app store release, or real customer use.
