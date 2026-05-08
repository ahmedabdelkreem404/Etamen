# Sprint 27 Real Device / Emulator Walkthrough Report

## Walkthrough Context

- Date/time: 2026-05-06, Africa/Cairo.
- Device used: Android emulator `emulator-5554`.
- Device profile observed: `sdk gphone64 x86 64`, Android API 36.
- Build type: local debug build.
- Flutter API base URL used by app: `http://10.0.2.2:8000/api/v1`.
- Host backend health URL checked: `http://127.0.0.1:8000/api/v1/system/health`.
- Test patient account used: `sprint26195627@example.com`.
- Test password: `Password1234`.

## Legacy App Reference Status

The legacy Doctor Finder project was found at `I:/Etamen/doctorfinder_timeslot-main`.

It was inspected as visual reference only. Running it on the current Flutter/Gradle toolchain was attempted but failed because the legacy Android project still applies Flutter Gradle plugins imperatively. No old networking, auth, payment, chat, notification, or hardcoded ID code was copied.

Useful visual references taken from the old app and provided screenshot:

- Teal/cyan primary tone, especially old `Color(0xff01d8c9)`.
- White rounded cards on soft backgrounds.
- Clear doctor booking hierarchy.
- Icon-led bottom navigation and doctor cards.

## Environment Status

| Area | Status | Notes |
| --- | --- | --- |
| Backend health | PASS | `/system/health` reachable from host. |
| Emulator API URL | PASS | Flutter uses `10.0.2.2` for emulator loopback. |
| Login API | PASS | Test patient login succeeded. |
| `/me` after login | PASS | Account screen showed patient data. |
| Session restore | PASS | App reopened to protected Home after force stop. |
| Logout | PASS | Confirm dialog shown, local session cleared, app returned to login. |
| Doctors data | BLOCKED | `/doctors` returned 0 approved doctors. |
| Payment methods | BLOCKED | `/payment-methods` returned 0 active methods. |
| Pharmacies | BLOCKED | `/pharmacies` returned 0 approved pharmacies. |
| Labs | BLOCKED | `/labs` returned 0 approved labs. |
| Care plans | BLOCKED | `/care-plans` returned 0 plans. |
| Notifications | PARTIAL | Endpoint reachable, returned 0 notifications. |
| AI | PARTIAL | Conversations endpoint reachable, no full prompt walkthrough completed. |

## Walkthrough Results By Flow

| Flow | Status | Issue Found | Fix Applied | Remaining Note |
| --- | --- | --- | --- | --- |
| Auth / Session | PASS | None blocking. | Verified login, session restore, logout. | Retest on real physical device with LAN/staging URL. |
| Home / Navigation UX | PASS | Quick action cards overflowed on emulator. | Reduced quick-card density and restored old teal-inspired palette. | Continue visual QA after seed data exists. |
| Doctor Booking | PARTIAL | Doctors list reachable but empty. | None; data issue. | Seed approved doctor, specialty, branch, slots, fee. |
| Manual Payment | NOT TESTED | No paid appointment/payment methods. | None; data issue. | Seed active manual methods and create pending payment appointment. |
| My Appointments | PARTIAL | Endpoint reachable but no appointments. | None; data issue. | Needs booked appointment. |
| Pharmacy | PARTIAL | Pharmacies endpoint returned empty. | None; data issue. | Seed pharmacy/products/prescription product. |
| Labs | PARTIAL | Labs endpoint returned empty. | None; data issue. | Seed lab/tests/results. Flutter uses actual `/lab/orders` endpoints. |
| Health / Vitals | PARTIAL | Health profile reachable; full add-vital walkthrough not completed in this pass. | None. | Retest form entry after reinstall/build. |
| Medications | PARTIAL | Reminders endpoint reachable but empty. | None; data issue. | Create reminder during seeded walkthrough. |
| Care Plans | PARTIAL | No active plan for patient. | None; data issue. | Assign/create active care plan. |
| Notifications | PARTIAL | List reachable but empty. | None; data issue. | Seed notification or trigger one through backend flow. |
| AI Assistant | PARTIAL | No full provider/refusal/red-flag test completed. | None. | Confirm fake/local/unavailable provider mode. |
| Account / Legal / Support | PASS | Account page loaded and logout worked. | None. | Legal/support pages still need final legal/contact review. |

## Screenshots Captured

Screenshots and UI dumps were captured under `I:/Etamen/.tmp/`.

Key captured files:

- `etamen-home-after-login.png`
- `etamen-home-fixed2.png`
- `etamen-services.png`
- `etamen-doctors.png`
- `etamen-session-restore2.png`
- `etamen-account-s27.png`
- `etamen-home-teal-s27.png`
- `window-logout-dialog-s27.xml`
- `window-after-logout-s27.xml`

Some screenshots captured through PowerShell redirection may not preview in all tools; emulator UI XML confirms the tested states.

## Blockers Before Full Walkthrough

- Pilot seed data is missing for doctors, payments, pharmacy, labs, care plans, and notifications.
- Manual payment review cannot be tested until payment methods and a pending payment appointment exist.
- Pharmacy/lab order payment cannot be tested until provider catalog data exists.
- Lab result download cannot be tested until a result file exists.
- AI safety paths require known backend provider mode and prompts.

## Sprint 27 Decision

The Flutter shell, auth/session behavior, main navigation, and visual polish are working on emulator. The app is **not yet ready to invite the first 20 pilot users** because the primary business flows cannot be proven without pilot seed/admin data.

Exact condition to invite supervised pilot users:

1. Seed at least one approved doctor with slots and fee.
2. Seed active Vodafone Cash and InstaPay payment methods with instructions.
3. Seed at least one pharmacy and lab with active products/tests.
4. Create/assign one care plan and one notification for the test patient.
5. Rerun flows A through M and pass booking, manual payment, pharmacy order, lab order, health, logout, and legal/support checks.

---

# Sprint 28 Seeded Walkthrough Update

## Walkthrough Context

- Date/time: 2026-05-06, Africa/Cairo.
- Device used: Android emulator `emulator-5554`.
- Build type: local debug APK.
- Flutter API base URL: `http://10.0.2.2:8000/api/v1`.
- Backend host health URL: `http://127.0.0.1:8000/api/v1/system/health`.
- Demo patient account: `pilot.patient@example.test`.
- Demo password: `Password1234`.
- Backend data source: `PilotDemoSeeder`.

## Environment And Seed Status

| Area | Status | Evidence / Notes |
| --- | --- | --- |
| Backend migrations | PASS | `migrate:fresh --seed` passed after MySQL-compatible index/timestamp/AI-config schema fixes. |
| Pilot demo seeder | PASS | `php artisan db:seed --class=PilotDemoSeeder` creates local/staging demo data only. |
| Patient login API | PASS | Host API login returns `pilot.patient@example.test`. |
| Doctors | PASS | One approved demo doctor is returned by `/doctors`. |
| Doctor slots | PASS | `/doctors/{id}/slots` returns available slots; Flutter was fixed to use `limit`. |
| Payment methods | PASS | Vodafone Cash and InstaPay manual methods are active with fake local-only instructions. |
| Pharmacy | PASS | One approved pharmacy and two active products are returned. |
| Labs | PASS | One approved lab, two tests, one package, and one demo result are seeded. |
| Health | PASS | Health profile and latest vitals are seeded. |
| Medications | PASS | A twice-daily medication reminder produces today schedule items. |
| Care plans | PASS | One active nutrition care plan with meals, foods, and instructions is seeded. |
| Notifications | PASS | One safe demo notification is seeded. |
| AI | PARTIAL | No AI secrets are seeded; provider mode must be checked from backend config during manual pass. |

## Sprint 28 Walkthrough Results

| Flow | Status | Evidence / Screenshot | Issue | Fix | Remaining Action |
| --- | --- | --- | --- | --- | --- |
| Login / Home | PASS | `I:/Etamen/.tmp/pilot-screenshots/01-login.png`, `02-home.png` | None in first seeded pass. | None. | Retest after every database reset with cleared app data. |
| Services tab | PASS | `03-services.png` | None. | None. | Continue manual visual QA. |
| Doctors list | PASS | `04-doctors.png` | Demo doctor visible. | Seeder added approved doctor and public profile. | None. |
| Doctor profile / slots | PARTIAL -> FIXED | `05-doctor-profile.png`, `06-booking.png` | Slots area showed a network error because Flutter sent `per_page` but backend expects `limit`. | Updated doctor remote data source query parameter to `limit`. | Manually retest booking after clean login. |
| Manual payment | NOT TESTED | Pending screenshot. | Full appointment/payment path was not completed after slots fix. | Payment methods are now seeded. | Book appointment, choose manual method, upload proof, admin review. |
| My appointments | NOT TESTED | Pending screenshot. | Needs booked appointment. | Seeder now enables booking prerequisites. | Retest after booking. |
| Pharmacy | NOT TESTED | Pending screenshot. | Order creation not completed in automated pass. | Seeder added pharmacy/products. | Create order with and without prescription. |
| Labs | NOT TESTED | Pending screenshot. | Order creation/result download not completed in automated pass. | Seeder added lab/tests/package/demo result. | Create branch/home orders and test result download. |
| Health / Vitals | PARTIAL | Pending screenshot. | Seeded data exists; add-vital form not completed in automated pass. | Seeder added vitals/profile. | Add new blood pressure/blood sugar/weight manually. |
| Medications | PARTIAL | Pending screenshot. | Today schedule seeded; taken/skipped not completed. | Seeder added reminder. | Mark taken/skipped manually. |
| Care Plans | PARTIAL | Pending screenshot. | Plan seeded; check-in/meal log not completed. | Seeder added active plan. | Submit check-in and meal log. |
| Notifications | PARTIAL | Pending screenshot. | Notification seeded; read/read-all/delete not completed. | Seeder added welcome notification. | Retest badge and read actions. |
| AI Assistant | PARTIAL | Pending screenshot. | Provider/refusal/red-flag paths not exercised. | No secrets added. | Verify configured provider mode and prompts manually. |
| Account / Legal / Support | PARTIAL | Existing Sprint 27 evidence. | Not repeated after final DB reset. | None. | Retest account/legal/language/logout in final manual pass. |

## Issues Found And Fixed

- Backend MySQL migration failed on overly long auto-generated index names. Fixed by adding explicit shorter index names in affected appointment, pharmacy, medication, and care plan migrations.
- Backend MySQL migration rejected non-null appointment timestamp fields with no default. Fixed by using `dateTime` for doctor holiday and appointment slot start/end values.
- Baseline AI provider seeding failed because encrypted config data was stored in a JSON column. Fixed by using text storage for Laravel's encrypted cast output, without adding any AI secrets.
- Doctor slots failed in Flutter because the app used `per_page` for a backend endpoint that accepts `limit`. Fixed in the doctors remote data source.
- Stale Flutter tokens after `migrate:fresh` caused unauthenticated protected routes. Documented app data clearing/reinstall after database resets.

## Automation Limitation

After the database reset and app data clear, ADB text entry repeatedly truncated the `.test` email in the emulator. The backend credentials were verified through the host API, but a full post-fix click-by-click walkthrough still needs manual credential entry on emulator or a physical Android device.

## Sprint 28 Decision

Decision: **Ready after one manual seeded walkthrough pass, not ready to invite the first 20 users yet.**

The seed data and two blocking technical issues are fixed. The exact remaining condition is one successful manual pass through booking, manual payment proof upload/admin review, My Appointments, pharmacy order, lab order/result, vitals, medications, care plans, notifications, AI safety prompts, account/legal/support, and logout/session restore using `pilot.patient@example.test`.

---

# Sprint 32 Seeded E2E Walkthrough

## Context

- Date: 2026-05-07.
- Device: Android emulator `emulator-5554` using `Pixel_8_Pro` AVD.
- App package: `com.etamen.etamen_app`.
- Screenshot build: debug APK for `android-x64`.
- Backend URL in app: `http://10.0.2.2:8000/api/v1`.
- Backend host: `http://127.0.0.1:8000`.
- Demo account: `pilot.patient@example.test` / `Password1234`.
- Screenshot folder: `I:/Etamen/.tmp/sprint32-final-screenshots/`.

## Results

| Flow | Result | Screenshot | Notes | Remaining blocker |
| --- | --- | --- | --- | --- |
| Login | PASS | `00-login-clear.png` | Login succeeded after correcting ADB email entry for `@`. | Repeat on physical device. |
| Session restore | PASS | `01-home.png` | App relaunched and restored the patient session. | None on emulator. |
| Home | PASS | `01-home.png` | Old-style hero/search/doctor emphasis visible. | Product-owner review. |
| Doctors list | PASS | `03-doctors-list-with-avatar.png` | Avatar placeholder URL, visible rating summary, branch/city/fee shown. | Real licensed doctor photos still missing. |
| Doctor profile | PASS | `04-doctor-profile-with-avatar.png` | Avatar, rating, fee, location, about, and slots visible. | Real photo/review content for production. |
| Booking slot selection | PASS | `05-booking-slot-selection.png` | Selected slot uses orange state and CTA is visible above system navigation. | Physical small-screen recheck. |
| Booking submission | PASS | `06-booking-confirmation.png` | Booking submitted and moved to payment flow; appointment appeared in My Appointments. | None on emulator. |
| Payment method selection | PASS | `07-payment-methods.png` | Friendly method cards and summary shown; no raw backend status. | Admin payment operation still needs staff workflow. |
| Manual proof upload | PARTIAL | `08-payment-proof-upload.png` | Upload screen reached; file picker upload was not completed in automated pass. | Must upload a real test image on physical device. |
| Admin payment review | NOT TESTED | N/A | Outside Flutter patient app. | Filament/admin operator walkthrough required. |
| My appointments | PASS | `09-my-appointments.png` | New pending-payment appointment visible with friendly copy. | None on emulator. |
| Pharmacy products/order | PARTIAL | `10-pharmacy-products.png` | Pharmacy list and product entry shown; order was not completed. | Complete order E2E before wider pilot. |
| Lab tests/order/result | PARTIAL | `11-labs-tests.png` | Lab list and test entry shown; order/result flow not completed. | Complete lab order/result E2E. |
| Vitals / health dashboard | PASS | `12-health-dashboard.png` | Health hub loads with non-diagnostic wording. | Add-vital action not exercised. |
| Medication reminder today | PASS | `13-medications-today.png` | Demo medication and adherence summary visible. | Notification timing needs real-device QA. |
| Care plan check-in / meal log | PARTIAL | `14-care-plan.png` | Demo care plan visible; check-in/meal log not submitted. | Complete care-plan actions. |
| Notifications | PASS | `15-notifications.png` | Notification center loads seeded notification. | Push delivery remains production setup item. |
| AI safe/refusal/red-flag prompts | PARTIAL | `16-ai-chat.png` | AI safety disclaimer/entry visible. Prompts were not completed. | Exercise prompts with configured AI backend. |
| Account/legal/support | PASS | `17-account.png` | Account and legal/support entry points visible. | Final legal/owner approval. |
| Logout | NOT TESTED | N/A | Session restore was verified; logout was not completed. | Include in final physical-device smoke test. |
| Website landing | PASS | `18-website-landing.png` | First viewport captured with Doctor Finder style hero/search/service cards. | Full old website remains out of scope. |

## Decision

Core doctor booking path is **PASS on seeded emulator**. The app should not be treated as fully pilot-cleared until a physical-device pass completes payment proof upload, admin payment review, and logout.

## Sprint 32 Validation Commands

| Command | Result | Notes |
| --- | --- | --- |
| Backend `php artisan migrate:fresh --seed` | PASS | Ran on the Etamen local database before final tests. |
| Backend `php artisan db:seed --class=PilotDemoSeeder` | PASS | Recreated seeded visual walkthrough data. |
| Backend `php artisan test` | PASS | 196 tests / 1642 assertions. |
| Flutter `flutter pub get` | PASS | Dependency resolution completed. |
| Flutter `dart format .` | PASS | 0 files changed after final lint cleanup. |
| Flutter `flutter analyze` | PASS | No issues found. |
| Flutter `flutter test` | PASS | 162 tests passed. |
| Flutter ARM64 debug APK build | PASS | Built `build/app/outputs/flutter-apk/app-debug.apk`. |
| `git diff --check` | PASS | No whitespace errors; Windows line-ending warnings only. |

---

# Sprint 33 Physical Device Verification Attempt

Date: 2026-05-08

## Summary

**Result: BLOCKED / NOT TESTED on physical Android.**

ADB was available through the local Android SDK path, but only the emulator was detected:

```text
emulator-5554 device product:sdk_gphone64_x86_64 model:sdk_gphone64_x86_64 device:emu64xa
```

Sprint 33 explicitly requires a real Android phone, so the emulator was not used as substitute evidence.

## Prepared Artifacts

- Screenshot folder: `I:/Etamen/.tmp/sprint33-physical-device-screenshots/`.
- ARM64 APK: `I:/Etamen/etamen-flutter/build/app/outputs/flutter-apk/app-debug.apk`.
- APK backend define: `ETAMEN_API_BASE_URL=http://192.168.1.5:8000/api/v1`.
- Intended phone backend URL: `http://192.168.1.5:8000/api/v1`.
- Package: `com.etamen.etamen_app`.

## Walkthrough Results

| Flow | Result | Evidence | Blocker |
| --- | --- | --- | --- |
| Physical device detection | FAIL | ADB listed emulator only. | Yes. |
| Phone backend reachability | NOT TESTED | No phone on ADB. | Yes. |
| Fresh install / app data clear | NOT TESTED | No phone. | Yes. |
| Login | NOT TESTED | No phone. | Yes. |
| Session restore | NOT TESTED | No phone. | Yes. |
| Doctor booking | NOT TESTED | No phone. | Yes. |
| Real proof upload | NOT TESTED | No phone gallery/camera image. | Yes. |
| Admin review same payment | NOT TESTED | No phone-created payment proof. | Yes. |
| Flutter verified/confirmed state | NOT TESTED | No admin-reviewed payment. | Yes. |
| Logout | NOT TESTED | No phone. | Yes. |
| Pharmacy/lab basics | NOT TESTED | No phone. | No, only if explicitly scoped out later. |
| AI/health/notifications smoke | NOT TESTED | No phone. | No for doctor-only pilot unless navigation/security fails later. |

## Decision

Sprint 33 cannot approve pilot invitations. The current decision is **NOT_READY_DUE_BLOCKERS** until a physical-device proof upload, admin accept, Flutter refresh, and logout/session restore pass with screenshots.
