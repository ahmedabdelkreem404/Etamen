# Sprint 32 Final Screenshots

Date: 2026-05-07

## Capture Context

- Device: Android emulator `emulator-5554` using the `Pixel_8_Pro` AVD.
- App package verified: `com.etamen.etamen_app`.
- App version shown by Android package manager: `1.0.0` / versionCode `1`.
- Screenshot build ABI: `android-x64` debug build for emulator capture.
- Requested validation build ABI: `android-arm64` debug build passed separately.
- Backend URL used by the installed APK: `http://10.0.2.2:8000/api/v1`.
- Local backend host verified: `http://127.0.0.1:8000`.
- Demo account: `pilot.patient@example.test`.
- Final APK validation artifact: `I:/Etamen/etamen-flutter/build/app/outputs/flutter-apk/app-debug.apk`.

## Screenshot Files

All final Sprint 32 screenshots are stored under:

`I:/Etamen/.tmp/sprint32-final-screenshots/`

| Required file | Status | Notes |
| --- | --- | --- |
| `01-home.png` | Captured | Home loaded after login with seeded doctor visual cards. |
| `02-services.png` | Captured | Services hub with doctor, pharmacy, lab cards. |
| `03-doctors-list-with-avatar.png` | Captured | Doctor list shows safe generated avatar, real demo rating summary, location, fee, and CTA. |
| `04-doctor-profile-with-avatar.png` | Captured | Doctor profile shows avatar, rating, fee, location, and slots. |
| `05-booking-slot-selection.png` | Captured | Slot selected with orange state and sticky confirmation action visible. |
| `06-booking-confirmation.png` | Captured | Booking was submitted and app moved to payment selection state. |
| `07-payment-methods.png` | Captured | Payment methods and summary captured. |
| `08-payment-proof-upload.png` | Captured | Manual proof upload screen reached; actual file picker upload not automated in this pass. |
| `09-my-appointments.png` | Captured | Appointments tab shows newly created pending-payment appointment. |
| `10-pharmacy-products.png` | Captured | Pharmacy list with richer demo names and product entry point. |
| `11-labs-tests.png` | Captured | Lab list with richer demo names and test entry point. |
| `12-health-dashboard.png` | Captured | Health hub with vitals, medications, care plan, AI entries. |
| `13-medications-today.png` | Captured | Medication reminder/today screen with demo medication. |
| `14-care-plan.png` | Captured | Care plan list with demo nutrition plan. |
| `15-notifications.png` | Captured | Notification center with seeded notification. |
| `16-ai-chat.png` | Captured | AI assistant entry/disclaimer screen. |
| `17-account.png` | Captured | Account/legal/support entry points and session user. |
| `18-website-landing.png` | Captured | Laravel landing first viewport with Doctor Finder style hero. |

## Capture Notes

- The emulator showed one Android System UI ANR dialog before the final login attempt. It was dismissed with `Wait`; the Etamen app remained responsive afterward.
- The first ADB email entry attempt used an escaped `@` sequence incorrectly and was retried. Final login succeeded by entering `pilot.patient`, sending Android `KEYCODE_AT`, then entering `example.test`.
- Screenshot capture intentionally did not use old Doctor Finder app screenshots as new app evidence.
- Payment proof file selection remains a real-device/manual QA item because the Android picker flow was not completed during this automated pass.
- ARM64 APK build initially failed because `GRADLE_USER_HOME` pointed to a full `D:/gradle_home`; rerunning the build with the session Gradle cache on the user profile disk succeeded. This was an environment/storage issue, not an app code issue.
