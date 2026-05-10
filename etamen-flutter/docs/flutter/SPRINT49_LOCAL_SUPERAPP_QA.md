# Sprint 49 Local Super App QA

Date: 2026-05-10

## Scope

Sprint 49 was local emulator regression only.

No Hostinger, staging, SSH, deployment, or public launch work was performed.

## APK

Final local regression APK:

```text
I:\Etamen\.tmp\etamen-local-superapp-regression.apk
C:\Users\Ahmed Abdelkareem\OneDrive\Desktop\Etamen_Android_Website_Ready\etamen-local-superapp-regression.apk
```

Build target:

```text
ETAMEN_API_BASE_URL=http://10.0.2.2:8000/api/v1
ETAMEN_ENV=local
android-x64 debug
```

## Screenshots

All screenshots are stored under:

```text
I:\Etamen\.tmp\sprint49-local-superapp-regression\
```

Folders:

- `auth`
- `doctor`
- `hospital`
- `radiology`
- `gym`
- `coach`
- `pharmacy-lab`

## QA Result

### Auth / Session

PASS.

- Login worked.
- Home loaded.
- Services loaded.
- Account loaded.
- Logout worked.
- Reopen after logout showed the logged-out login state.

### Direct Doctor Flow

PASS.

- Doctors list loaded.
- Doctor profile opened.
- Slot selection worked.
- Booking created.
- Payment methods loaded.
- Vodafone Cash selection worked.
- Proof image was selected from the emulator picker.
- Proof upload succeeded.
- Local admin accepted the same payment.
- Appointment refreshed to confirmed/paid state.

### Hospital Context

PASS.

- Hospitals entry worked.
- Hospital list loaded.
- Hospital details loaded.
- Department doctors loaded.
- Doctor opened with hospital context.
- Booking reached payment methods.
- Backend stored validated hospital context.

### Radiology

PASS.

- Radiology entry worked.
- Categories/scans loaded.
- Scan selection worked.
- Order creation worked.
- Payment methods loaded.
- Proof upload worked.
- Local admin accept changed order to paid.
- Visible result metadata appeared.
- Download/open action completed safely.

### Gym

PASS.

- Gyms entry worked.
- Gym list and details loaded.
- Membership booking was created.
- Payment methods loaded.
- Proof upload worked.
- Local admin accept changed booking to confirmed.
- Flutter refresh showed confirmed payment state.

### Coach

PASS.

- Coaches entry worked.
- Coach list and details loaded.
- Session booking was created.
- Payment methods loaded.
- Proof upload worked.
- Local admin accept changed booking to confirmed.
- Flutter refresh showed confirmed payment state.

### Pharmacy / Lab

SMOKE PASS.

- Pharmacy list opened and loaded demo pharmacies.
- Lab list opened and loaded demo labs.

Full pharmacy/lab payment E2E was not part of this hardening pass.

## Security / Privacy

PASS for checked patient responses.

No raw proof paths, raw result paths, private storage paths, payment config, Paymob secrets, provider private documents, national ID documents, tax/commercial/bank documents, admin notes, or internal contract terms were found in the patient-facing responses checked during Sprint 49.

## Tests / Build

Backend:

- `php artisan test`: PASS, 244 tests.
- `git diff --check`: PASS.

Flutter:

- `flutter pub get`: PASS.
- `dart format .`: PASS, 0 files changed.
- `flutter analyze`: PASS.
- `flutter test`: PASS, 182 tests.
- Local debug APK build for `android-x64`: PASS.

## Remaining Limits

Not approved by this sprint:

- staging readiness
- real-phone readiness
- public launch readiness
- live payment readiness
- app store readiness

## Decision

```text
LOCAL_SUPERAPP_REGRESSION_ACCEPTED
```

## Next Step

Repeat the proven local doctor payment proof and admin review flow on staging and a real Android phone after staging data and server readiness are fixed.
