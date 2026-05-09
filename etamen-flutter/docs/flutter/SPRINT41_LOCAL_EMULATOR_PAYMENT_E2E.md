# Sprint 41 Local Emulator Payment E2E

Date: 2026-05-09

Scope: Android emulator only.

API base used by APK:

```text
http://10.0.2.2:8000/api/v1
```

## APK

Built APK:

```text
I:\Etamen\.tmp\etamen-local-payment-proof-admin-gate.apk
```

Build details:

- Target platform: `android-x64`.
- Environment: `local`.
- Size: `44.06 MB`.
- SHA-256: `91E948539DFB6898215552B227968159676053C2FD435A778DEAD63A85524477`.
- Flutter debug assets verified.
- ABI: `x86_64`.

## Emulator Flow Result

Device:

```text
emulator-5554
```

| Step | Result | Evidence |
| --- | --- | --- |
| Launch app | PASS | `01-login.png` |
| Login | PASS | `02-home.png` |
| Home loads | PASS | `02-home.png` |
| Doctors list | PASS | `03-doctors-list.png` |
| Doctor profile | PASS | `04-doctor-profile.png` |
| Slot selection | PASS | `05-booking-slot.png` |
| Booking submit | PASS | `06-payment-methods.png` |
| Payment methods show Vodafone/InstaPay | PASS | `06-payment-methods.png` |
| Vodafone Cash selected | PASS | `07-vodafone-selected.png` |
| Proof upload screen | PASS | `08-proof-upload-screen.png` |
| Image picker opened | PASS | `08a-image-picker-open.png` |
| Real emulator image selected | PASS | `08b-after-image-selected.png` |
| Proof sent from app | PASS | `09-proof-uploaded-pending-review.png` |
| Pending review after refresh | PASS | `09b-payment-status-after-refresh.png` |
| My appointments pending review | PASS | `10-my-appointments-pending-review.png` |
| Admin accept reflected in app | PASS | `13-app-appointment-after-admin-accept.png` |
| Logout | PASS | `14-after-logout.png` |
| Reopen logged out | PASS | `14b-after-reopen-logged-out.png` |
| Re-login confirmed state | PASS | `15-after-relogin-appointment-confirmed.png` |

Screenshot folder:

```text
I:\Etamen\.tmp\sprint41-local-payment-e2e\
```

## Proof Image

Local test image:

```text
I:\Etamen\.tmp\test-proof.jpg
```

Pushed to emulator:

```text
/sdcard/Download/test-proof.jpg
```

It was selected through Android Photo Picker and uploaded from the Flutter app.

## Admin Review Result

Admin accept was performed on the same payment created by the emulator.

Result:

- payment status became `verified`.
- appointment status became `confirmed`.
- payment proof became `accepted`.
- invoice was created.
- audit log was created.
- Flutter showed the appointment as confirmed after refresh/reopen.

## Security / Privacy

Patient-facing responses were checked and did not expose:

- private proof paths
- `medical_private`
- `storage/private`
- payment config
- Paymob secrets
- inactive Paymob method

## Tests / Build

Flutter:

- `flutter pub get`: PASS.
- `dart format .`: PASS, 0 changed.
- `flutter analyze`: PASS.
- `flutter test`: PASS, all tests passed.
- local APK build: PASS.

Backend:

- `php artisan test`: PASS, `218 passed`.

## Final Decision

```text
LOCAL_PAYMENT_E2E_ACCEPTED
```

This proves only the local emulator E2E path. It does not prove Hostinger/staging, real Android phone upload, or public launch readiness.
