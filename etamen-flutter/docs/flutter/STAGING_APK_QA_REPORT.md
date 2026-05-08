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
