# Sprint 47 Local Fitness Flutter QA

Date: 2026-05-10

Scope: local emulator only. No Hostinger, staging, SSH, or public deployment work was performed.

## Implemented Flutter UI

- Added `lib/features/fitness/` with models, repository, Riverpod controllers, widgets, and screens.
- Added Services entries for:
  - `الجيمات` / Gyms
  - `الكوتشات` / Coaches
- Added gym screens:
  - `GymsPage`
  - `GymDetailsPage`
  - `GymBookingDetailsPage`
  - `MyGymBookingsPage`
- Added coach screens:
  - `CoachesPage`
  - `CoachDetailsPage`
  - `CoachBookingDetailsPage`
  - `MyCoachBookingsPage`
- Extended the existing payment routes to preserve optional `gymBookingId` and `coachBookingId` context.
- Fixed fitness JSON unwrapping so booking responses are not accidentally replaced by nested `session_type`, `membership_plan`, or `gym_class` objects.

## APIs Consumed

Gym:

- `GET /api/v1/gyms`
- `GET /api/v1/gyms/{gym}`
- `GET /api/v1/gyms/{gym}/membership-plans`
- `GET /api/v1/gyms/{gym}/classes`
- `GET /api/v1/gym/bookings`
- `POST /api/v1/gym/bookings`
- `GET /api/v1/gym/bookings/{booking}`
- `POST /api/v1/gym/bookings/{booking}/cancel`

Coach:

- `GET /api/v1/coaches`
- `GET /api/v1/coaches/{coach}`
- `GET /api/v1/coaches/{coach}/session-types`
- `GET /api/v1/coaches/{coach}/availability`
- `GET /api/v1/coaches/{coach}/packages`
- `GET /api/v1/coach/bookings`
- `POST /api/v1/coach/bookings`
- `GET /api/v1/coach/bookings/{booking}`
- `POST /api/v1/coach/bookings/{booking}/cancel`

Payment reuse:

- `GET /api/v1/payment-methods`
- `POST /api/v1/payments/{payment}/manual/select`
- `POST /api/v1/payments/{payment}/proofs`
- `GET /api/v1/payments/{payment}/status`

## Local Backend Prep

Verified locally:

- `GET http://127.0.0.1:8000/api/v1/system/health` returned 200.
- `GET /api/v1/gyms` returned the demo gym.
- `GET /api/v1/coaches` returned demo fitness/nutrition coaches.
- `GET /api/v1/payment-methods` returned active Vodafone Cash and InstaPay without Paymob secrets.

## Emulator QA Result

Screenshots:

```text
I:\Etamen\.tmp\sprint47-local-fitness\
```

APK:

```text
I:\Etamen\.tmp\etamen-local-fitness.apk
C:\Users\Ahmed Abdelkareem\OneDrive\Desktop\Etamen_Android_Website_Ready\etamen-local-fitness.apk
```

Passed on emulator:

- App launches with local API build.
- Services tab shows Gyms and Coaches entries.
- Coaches list loads from local backend.
- Coach details loads from local backend.
- Backend creates coach booking and payment when booking request succeeds.

Not fully passed:

- Gym booking to payment/proof/admin accept was not completed from Flutter emulator.
- Coach booking reached booking creation during QA, but payment method/proof/admin accept was not completed from Flutter emulator before this report.
- The first QA run exposed a Flutter JSON unwrapping bug where booking details showed `حالة غير معروفة`; this was fixed in the code.

## Tests And Build

Latest verification:

- `dart format` PASS.
- `flutter analyze` PASS.
- `flutter test test/fitness_test.dart` PASS.
- `flutter build apk --debug --target-platform android-x64 --dart-define=ETAMEN_API_BASE_URL=http://10.0.2.2:8000/api/v1 --dart-define=ETAMEN_ENV=local` PASS.

Full regression should still be run before accepting the sprint gate:

- `flutter test`
- `php artisan test`
- `git diff --check`

## Decision

```text
LOCAL_FITNESS_PAYMENT_UI_BLOCKED
```

Reason: the patient-facing Fitness UI foundation is implemented and builds, but the required gym + coach payment proof + admin accept E2E was not completed from Flutter on emulator.

## Remaining Blockers

- Complete Flutter gym booking -> payment methods -> manual proof upload -> admin accept -> paid/confirmed refresh.
- Complete Flutter coach booking -> payment methods -> manual proof upload -> admin accept -> paid/confirmed refresh.
- Capture the remaining proof/payment/admin screenshots.
- Add a focused widget/integration test that proves booking creation navigates to the payment route when `payment_id` is returned.

## Next Sprint Recommendation

Sprint 48 should be a local Fitness E2E hardening sprint only:

- verify the fixed JSON unwrap in full emulator flow.
- add route-level tests for gym/coach payment navigation.
- complete proof upload/admin accept for both gym and coach.
- only then move toward staging QA.

---

# Sprint 48 Follow-up

Date: 2026-05-10

Sprint 48 completed the missing local emulator payment E2E gate for both Gym and Coach.

Fixes:

- Flutter gym booking payload now sends backend-required `provider_id`.
- Gym plan/class booking now preserves the selected gym provider context.
- Payment status parsing now accepts nested `payment_method.type` as a safe fallback.

Local E2E results:

- Gym booking -> Vodafone Cash -> proof upload -> admin accept -> Flutter confirmed state: PASS.
- Coach booking -> Vodafone Cash -> proof upload -> admin accept -> Flutter confirmed state: PASS.
- Patient API leak check for gym/coach booking/payment responses: PASS.

Evidence:

```text
I:\Etamen\.tmp\sprint48-local-fitness-e2e\
```

APK:

```text
I:\Etamen\.tmp\etamen-local-fitness-e2e.apk
C:\Users\Ahmed Abdelkareem\OneDrive\Desktop\Etamen_Android_Website_Ready\etamen-local-fitness-e2e.apk
```

Sprint 48 decision:

```text
LOCAL_FITNESS_PAYMENT_E2E_ACCEPTED
```

Still not approved:

- staging readiness.
- public launch readiness.
- real-phone readiness.
