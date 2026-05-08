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
