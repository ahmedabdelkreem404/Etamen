# Etamen Flutter Pilot Readiness Report

## Readiness Estimate

Current estimate: **72% ready for a limited supervised pilot**.

This means the Flutter app can be handed to internal testers or a small pilot group only after the backend is running on staging/pilot, seed data exists, support/legal details are configured, and the real-device E2E checklist passes.

## Completed Feature Areas

- Auth, register, login, session restore, and logout foundation.
- Doctors discovery, profile, slots, booking, and appointment details.
- Manual/Paymob payment UI foundation for appointments, pharmacy, and labs.
- Pharmacy MVP: pharmacies, products, cart, prescription image upload, orders.
- Labs MVP: labs, tests/packages, cart, orders, result download foundation.
- Health/vitals tracking with non-diagnostic safety copy.
- Medication reminders, today medications, logs, adherence, refill foundation.
- Care plans/nutrition consumption, check-ins, meal logs, progress.
- In-app notifications, badge, read/delete, preferences, token foundation.
- AI assistant chat, context preview/toggle, refusal/red-flag UI.
- Account/settings/legal/support/about pages.
- Android hardening: app label, main internet permission, splash color, NDK pin.
- Pilot documentation and E2E checklist.

## Real Device Test Status

- Emulator smoke test passed on `emulator-5554` using `http://10.0.2.2:8000/api/v1`.
- Login succeeded with local patient test account `codexpatient@testlocal.com`.
- Home loaded, Doctors empty state displayed safely, Account page loaded `/me` data.
- Physical Android device `Infinix X657C` was detected earlier, but full real-device E2E is still required against LAN/staging URL.

## Remaining Pilot Blockers

- Staging/pilot backend URL with HTTPS must be available.
- Backend must run with `APP_DEBUG=false`.
- Pilot support email/phone/WhatsApp values must be configured.
- Legal text must be reviewed before any external pilot.
- Pilot providers, doctor schedules, pharmacy products, lab tests, and admin users must be prepared.
- Manual payment/admin review process must be staffed and tested.
- Real-device E2E checklist must pass.

## Remaining Public Launch Blockers

- Real Android release signing and Play Store packaging.
- App launcher icon replacement and full store assets.
- Live Paymob configuration/callback verification.
- Real FCM/APNS push setup.
- Production monitoring, crash reporting, backups, and log review.
- Refund/cancellation operational policy and automation decisions.
- Full legal review and privacy policy approval.

## Deferred Items

- Provider/admin dashboards in Flutter.
- Push notification production handling.
- Offline sync.
- Refund automation.
- Advanced maps/tracking.
- Complex charts/medical interpretation.
- Public marketing/site work.

## Security / Privacy Summary

No Flutter-side blocking secret exposure was found in the pilot audit. Flutter remains a patient app and does not verify payments, trust prices, call admin endpoints, send ownership IDs in create requests, expose private file paths, or claim diagnosis/treatment outcomes.

## Legal / Support Status

Legal pages and disclaimers exist as a draft foundation. They are appropriate for internal/pilot review, but they are **not final legal approval**. Support contacts are config-driven and should be set through dart-defines for staging/pilot.

## Build Status

Sprint 25 prepared native Android configuration for pilot builds.

- First full debug APK attempt failed because drive `I:` had insufficient free space during `mergeDebugNativeLibs`.
- The generated `etamen-flutter/build` folder was safely cleaned.
- A lighter real-device debug build succeeded with:
  `.\scripts\project_flutter.ps1 build apk --debug --target-platform android-arm64 --dart-define=ETAMEN_API_BASE_URL=http://10.0.2.2:8000/api/v1 --dart-define=ETAMEN_ENV=local`
- Output:
  `build/app/outputs/flutter-apk/app-debug.apk`

Real release signing remains a blocker before public distribution.

## Recommended Next Action

Run one complete real-device E2E pass using staging data and assign owners for each failed checklist item before inviting pilot users.
