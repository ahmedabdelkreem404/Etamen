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

## Sprint 27 Real Walkthrough Update

Sprint 27 performed an emulator walkthrough on `emulator-5554` using:

`http://10.0.2.2:8000/api/v1`

Validated successfully:

- Backend health reachable from host.
- Patient login with `sprint26195627@example.com`.
- Home loads after login.
- Session restore after app force-stop.
- Account page loads `/me` data.
- Logout confirmation clears local session and returns to login.
- Main shell navigation works for Home, Services, Doctors empty state, and Account.
- A visible Home quick-action overflow was fixed.
- The visual palette was refreshed closer to the legacy Doctor Finder teal/cyan experience.

Blocked or partial due missing seed data:

- Doctors endpoint returned zero approved doctors.
- Payment methods endpoint returned zero active methods.
- Pharmacies endpoint returned zero approved pharmacies.
- Labs endpoint returned zero approved labs.
- No appointments, pharmacy orders, lab orders, care plans, or notifications existed for the test patient.
- AI provider/refusal/red-flag paths were not fully exercised in this pass.

Sprint 27 decision:

**Not ready to invite the first 20 pilot users yet.**

Reason: the app shell/auth is working, but the core pilot flows cannot be proven until pilot seed/admin data exists.

Exact condition to invite supervised pilot users:

1. Seed approved doctor, specialty, branch, slots, and fee.
2. Seed active manual payment methods with instructions.
3. Seed at least one pharmacy/product and one lab/test.
4. Seed or create one care plan and one notification for the test patient.
5. Rerun the documented Sprint 27 walkthrough and pass booking, payment proof upload, appointment confirmation, pharmacy/lab order basics, logout/session restore, and support/legal review.

## Sprint 28 Seeded Demo Data Update

Sprint 28 added a local/staging-only `PilotDemoSeeder` and converted the missing-data blockers into concrete demo data:

- Patient: `pilot.patient@example.test` / `Password1234`.
- Approved demo doctor with Cardiology specialty, branch, schedule, fee, and slots.
- Active manual Vodafone Cash and InstaPay methods with fake local-only instructions.
- Approved pharmacy with one normal product and one prescription-required product.
- Approved lab with tests, package, and one demo lab-result file.
- Health profile, latest vitals, medication reminder, active care plan, and welcome notification.

Sprint 28 also fixed blockers discovered during setup:

- MySQL migration reset failed because of long generated index names in several tables. Affected indexes now have safe explicit names.
- MySQL rejected non-null appointment timestamp fields with no default. Doctor holiday and appointment slot start/end values now use `dateTime`.
- Baseline AI provider seeding failed because encrypted config data was stored in a JSON column. The column now uses text storage that matches Laravel's encrypted cast output, with no AI secrets added.
- Doctor slots failed in Flutter because the slots request used `per_page`; the backend endpoint expects `limit`. Flutter now sends `limit`.

Seed/API verification status:

- Host API login with `pilot.patient@example.test` passed.
- `/doctors`, `/doctors/{id}/slots`, `/payment-methods`, `/pharmacies`, `/pharmacies/{id}/products`, `/labs`, `/labs/{id}/tests`, `/health/profile`, `/health/vitals/latest`, `/medications/today`, `/care-plans`, and `/notifications` returned seeded data.

Sprint 28 real walkthrough status:

- Emulator launch/login/home/services/doctors list were verified with screenshots under `I:/Etamen/.tmp/pilot-screenshots/`.
- Doctor profile/booking reached a real slots mismatch, which was fixed.
- Full post-fix walkthrough remains pending because automated ADB text input repeatedly truncated the `.test` email after app data clearing. Credentials were verified through the backend API, so this is a walkthrough automation limitation, not a credential/backend failure.

Sprint 28 decision:

**Ready after one manual seeded walkthrough pass. Not ready to invite the first 20 supervised pilot users yet.**

Exact condition to invite users:

1. Clear app data or reinstall after `migrate:fresh`.
2. Manually login on emulator/real device with `pilot.patient@example.test`.
3. Complete and record booking, manual payment proof upload/admin review, appointment confirmation, pharmacy order, lab order/result, vitals, medications, care plan check-in/meal log, notifications, AI safety prompts, account/legal/support, logout/session restore.
4. If all pass with no blockers, move the decision to "Ready for supervised pilot".
