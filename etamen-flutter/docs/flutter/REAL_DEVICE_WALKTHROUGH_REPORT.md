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
