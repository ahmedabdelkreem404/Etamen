# Staging APK QA Report

Date: 2026-05-08

API base used:

- `https://etamen.inolty.com/api/v1`

This report contains no private credentials or tokens.

## Build Result

Commands completed:

- `flutter pub get`: PASS.
- `dart format .`: PASS, 0 files changed.
- `flutter analyze`: PASS.
- `flutter test`: PASS, 164 tests.
- `flutter build apk --debug --target-platform android-arm64 --dart-define=ETAMEN_API_BASE_URL=https://etamen.inolty.com/api/v1 --dart-define=ETAMEN_ENV=staging`: PASS after retrying with a temporary Gradle cache on `C:`.

Build complications:

- `D:\gradle_home` was full and initially blocked the APK build.
- Existing Gradle lock/daemon was stopped.
- `flutter clean` freed local workspace build artifacts.
- Build completed in the background after the first command timed out at the tool layer.

APK paths:

- Build output: `I:\Etamen\etamen-flutter\build\app\outputs\flutter-apk\app-debug.apk`
- QA copy: `I:\Etamen\.tmp\etamen-staging-debug.apk`

APK size:

- 50,336,810 bytes.

## Emulator Used

- Device: `sdk gphone64 x86 64`
- Device ID: `emulator-5554`
- Android: 16 / API 36
- App package: `com.etamen.etamen_app`

## Emulator Install Result

- APK install: PASS.
- App launch: PASS.
- Arabic login screen shown: PASS.

Screenshots:

- `I:\Etamen\.tmp\etamen-staging-emulator-install.png`
- `I:\Etamen\.tmp\etamen-staging-emulator-launch.png`
- `I:\Etamen\.tmp\etamen-staging-emulator-login-ready-slow.png`

## Emulator Login Result

Result: FAIL / BLOCKED.

Observed:

- The app accepted the staged demo email and password input.
- Login attempt showed the Arabic patient-facing error: `تعذر الاتصال بالسيرفر`.
- This matches the reported mobile symptom and must be investigated before manual QA sign-off.

Evidence:

- `I:\Etamen\.tmp\etamen-staging-emulator-home-or-error.png`

Notes:

- A first automated attempt touched the app before cold start completed and caused an Android ANR dialog.
- A slower retry waited for the login screen before entering data, avoiding the early-start interaction issue, but the login result still showed server connection failure.
- Local machine API login to the same staging URL succeeded, so the next investigation should compare Android network/TLS/Dio behavior against desktop HTTP behavior.

## External API Checks From Local Machine

These checks ran from the desktop, not inside the app:

- Landing `/`: 200.
- Landing `/?lang=en`: 200.
- Health `/api/v1/system/health`: 200.
- Readiness `/api/v1/system/readiness`: 500.
- Specialties `/api/v1/specialties`: 200.
- Doctors `/api/v1/doctors`: 200.
- Demo patient API login: 200; token was logged out immediately.

## Not Completed

- Full emulator doctor flow.
- Payment method flow.
- Real proof upload.
- Admin payment review.
- Physical Android phone test.
- APK upload to hosting.

## QA Decision

Current APK status:

- APK was built and installs.
- Emulator login is blocked by `تعذر الاتصال بالسيرفر`.
- Do not treat the APK as ready for manual product-owner QA until mobile login succeeds against staging.

Next action:

1. Get server SSH access and inspect staging logs/readiness 500.
2. Diagnose Android app login request against `https://etamen.inolty.com/api/v1`.
3. Rebuild APK after any backend/API/config fix.
4. Re-run emulator login and then physical-device proof upload/admin review.

---

# Sprint 38 Fixed APK QA

Date: 2026-05-08

This section supersedes the previous emulator login failure section for the newly rebuilt fixed APK.

## Root Cause Found

The staging API itself accepted login from desktop, and the rebuilt app also reached the staging API from Android. The old login failure was not reproduced after creating and using a valid staging QA patient account.

Safe debug/staging network logging was added for debug staging builds only. It logs request URL without query values, HTTP status, top-level response keys, response message, and Dio error type. It does not log passwords, tokens, authorization headers, payment proofs, or private health data.

## Fixed APK

API base compiled into the APK:

```text
https://etamen.inolty.com/api/v1
```

Environment:

```text
staging
```

APK package:

```text
com.etamen.etamen_app
```

Minimum Android:

```text
SDK 21
```

Native ABIs included:

- `armeabi-v7a`
- `arm64-v8a`
- `x86_64`

APK paths:

- `I:\Etamen\.tmp\etamen-staging-debug-fixed.apk`
- `C:\Users\Ahmed Abdelkareem\OneDrive\Desktop\Etamen_Android_Website_Ready\etamen-staging-debug-fixed.apk`

SHA-256:

```text
50C84AD26589FCAE5A632875534094E91FF5BD87F077C00A8E9EA09F82DDF37E
```

## Emulator QA Result

Emulator:

- ID: `emulator-5554`
- Model: Pixel 8 Pro profile
- ABI: `x86_64`
- Android API: 36

Results:

| Flow | Result | Notes |
| --- | --- | --- |
| Install APK | PASS | Universal debug APK installed successfully. |
| Launch app | PASS | Arabic login screen appeared. |
| Login | PASS | Staging API returned HTTP 200. |
| Home | PASS | Home loaded after login. |
| Doctors list | PARTIAL | Endpoint loaded, but staging currently has 0 approved doctors. |
| Doctor profile | NOT TESTED | Blocked by empty approved doctors data. |
| Booking/payment route | NOT TESTED | Blocked by empty approved doctors data. |
| Account | PASS | Account screen opened and showed staging build info. |
| Logout | PASS | Token cleanup and `/auth/logout` returned HTTP 200. |
| Logged-out restore | PASS | App returned to login screen after logout. |

Safe network log evidence:

- `I:\Etamen\.tmp\sprint38-staging-apk-qa\network-log-safe.txt`

Screenshots:

- `I:\Etamen\.tmp\sprint38-staging-apk-qa\01-login.png`
- `I:\Etamen\.tmp\sprint38-staging-apk-qa\02-home-after-login.png`
- `I:\Etamen\.tmp\sprint38-staging-apk-qa\03-doctors-list.png`
- `I:\Etamen\.tmp\sprint38-staging-apk-qa\06-account.png`
- `I:\Etamen\.tmp\sprint38-staging-apk-qa\07-after-logout.png`

## External API Checks

| Endpoint | Result |
| --- | --- |
| `/` | PASS, HTTP 200 |
| `/?lang=en` | PASS, HTTP 200 |
| `/api/v1/system/health` | PASS, HTTP 200 |
| `/api/v1/system/readiness` | FAIL, HTTP 500 |
| `/api/v1/specialties` | PASS, HTTP 200 |
| `/api/v1/doctors` | PASS, HTTP 200, empty data |
| `/api/v1/auth/login` | PASS, HTTP 200 |

## Remaining Blockers

- SSH/server access is still blocked.
- `/api/v1/system/readiness` still returns 500 and needs server logs.
- Staging has no approved doctors, so doctor profile and booking cannot be completed against the hosted API.
- Physical Android proof upload is still not tested.
- Admin payment review of the same phone-created payment is still not tested.

## Sprint 38 APK Decision

APK login gate:

- PASS on emulator.

Strict Sprint 38 decision:

- `STAGING_ACCESS_BLOCKED`

Reason:

- The app login problem is fixed on emulator, but the sprint cannot be signed as fully staging-ready while SSH is blocked and readiness is still 500.

---

# Sprint 39 Staging Doctor Booking + Payment Gate

Date: 2026-05-09

## APK / API Target

API base:

```text
https://etamen.inolty.com/api/v1
```

Package:

```text
com.etamen.etamen_app
```

## External API Result

| Check | Result |
| --- | --- |
| Login API | PASS |
| Home data after login | PASS |
| `/api/v1/doctors` | PASS, one approved staging doctor |
| Doctor slots | PASS, generated slots available |
| `/api/v1/payment-methods` | FAIL FOR FLOW, returns empty `data` |

## Emulator QA Result

Emulator:

- ID: `emulator-5554`
- Pixel 8 Pro profile
- Staging APK/API

| Flow | Result | Notes |
| --- | --- | --- |
| Launch app | PASS | Arabic login screen appears. |
| Login | PASS | Staging API login succeeds. |
| Home | PASS | Home loads and shows staging doctor data. |
| Doctors list | PASS | Approved staging doctor appears. |
| Doctor profile | PASS | Profile opens and shows available slots. |
| Booking slot selection | PASS | Slot can be selected. |
| Booking submit | PASS | Booking is created and payment step opens. |
| Payment methods | BLOCKED | Page opens but says no payment methods are available. |
| Proof upload screen | NOT REACHED | Blocked by empty payment methods. |
| My appointments | PASS | Appointment appears with friendly pending-payment state. |
| Account | PASS | Account page opens. |
| Logout | PASS | Logout returns to login screen. |

## Screenshots

Stored under:

```text
I:\Etamen\.tmp\sprint39-staging-doctor-payment-gate\
```

Key files:

- `01-login.png`
- `02-home.png`
- `03-doctors-list-with-doctor.png`
- `04-doctor-profile.png`
- `05-booking-slot.png`
- `06-booking-submitted.png`
- `07-payment-methods.png`
- `08-proof-upload-screen-blocked-no-payment-methods.png`
- `09-my-appointments.png`
- `10-account.png`
- `11-after-logout.png`
- `12-final-apk-launch.png`
- `13-final-apk-home-after-login.png`

## Important Note

`08-proof-upload-screen-blocked-no-payment-methods.png` is intentionally the payment-method empty state, not a real proof upload screen. The app cannot reach real proof upload until staging returns at least one active manual payment method.

## Current APK Deliverable

Target final copy:

```text
I:\Etamen\.tmp\etamen-staging-doctor-payment-gate.apk
C:\Users\Ahmed Abdelkareem\OneDrive\Desktop\Etamen_Android_Website_Ready\etamen-staging-doctor-payment-gate.apk
```

Build result:

- PASS.
- Size: `98.36 MB`.
- SHA-256: `09488C9701197E9A501AA3832FB4F675ED152FF15A4C2EDAD27645CE48B0DD01`.
- Native ABIs: `armeabi-v7a`, `arm64-v8a`, `x86_64`.
- Final copied APK installed successfully on emulator after build.
- Final copied APK launched to login and completed a fresh staging login on emulator.

## Sprint 39 QA Decision

Decision:

- `STAGING_PAYMENT_BLOCKED_NO_PAYMENT_METHODS`

Doctor discovery and booking now work on emulator against staging, but payment proof upload and admin review are blocked by missing staging payment methods.

---

# Sprint 40 Payment Methods + Proof Gate

Date: 2026-05-09

## Initial Payment Methods State

External check:

```text
GET https://etamen.inolty.com/api/v1/payment-methods
```

Result:

- HTTP `200`.
- Response shape is valid JSON.
- Returned `data: []`.
- Vodafone Cash is missing from staging.
- InstaPay is missing from staging.
- Paymob is not exposed, which is correct until real/sandbox configuration is verified.

## Local Backend Fix Prepared

The backend now has a safe repeatable way to prevent this blocker:

- `PaymentMethodSeeder` uses `updateOrCreate`.
- `manual_vodafone_cash` is active with staging-safe instructions.
- `manual_instapay` is active with staging-safe instructions.
- `paymob` remains inactive and has no secrets in config.
- Filament Payment Methods can create a missing method from the admin UI.
- Artisan command added:

```text
php artisan etamen:ensure-payment-methods --staging
```

Local command result:

```text
manual_vodafone_cash: active
manual_instapay: active
paymob: inactive
```

## Staging Activation Status

Staging activation was not completed from this machine because SSH remains blocked:

```text
Permission denied (publickey,password).
```

The hosted `/api/v1/payment-methods` endpoint still returns an empty list until the backend change is deployed and the seeder/command is run on the hosting account.

## APK Build

Sprint 40 APK:

```text
I:\Etamen\.tmp\etamen-staging-payment-methods-proof-gate.apk
C:\Users\Ahmed Abdelkareem\OneDrive\Desktop\Etamen_Android_Website_Ready\etamen-staging-payment-methods-proof-gate.apk
```

Build result:

- PASS.
- API base: `https://etamen.inolty.com/api/v1`.
- Size: `77.57 MB`.
- SHA-256: `F07C7E0A705F90B266719B92CE3EA839240A7327D231F827ED064C7A65C92C14`.
- Native ABIs: `armeabi-v7a`, `arm64-v8a`, `x86_64`.
- Flutter debug assets verified in APK:
  - `kernel_blob.bin`
  - `isolate_snapshot_data`
  - `vm_snapshot_data`

## Emulator Payment Flow Status

Because staging still returns no active payment methods, the Sprint 40 emulator payment proof gate cannot progress past the payment methods page yet.

| Step | Result | Notes |
| --- | --- | --- |
| Login | Previously PASS on staging APK | Sprint 39/38 verified. |
| Doctor list/profile/booking | Previously PASS | Staging doctor data exists. |
| Payment methods | BLOCKED | Hosted endpoint still returns `data: []`. |
| Proof upload screen | NOT REACHED | Requires active manual method. |
| Real proof upload | NOT TESTED | Requires owner phone after staging activation. |
| Admin review | NOT TESTED | No real proof exists yet. |

Sprint 40 APK install/launch check on emulator:

- Device: `emulator-5554`.
- Install: PASS.
- Launch: PASS.
- Screenshot:

```text
I:\Etamen\.tmp\sprint40-payment-methods-proof-gate\01-apk-installed-launched.png
```

## Sprint 40 Decision

Decision:

- `STAGING_PAYMENT_METHODS_STILL_BLOCKED`

The code fix and APK are ready, but the hosted staging database still needs the payment-method activation command/admin action.
