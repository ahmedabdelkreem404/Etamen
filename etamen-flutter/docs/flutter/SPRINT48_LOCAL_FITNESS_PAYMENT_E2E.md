# Sprint 48 Local Fitness Payment E2E

Date: 2026-05-10

## Scope

Sprint 48 was local-only.

It did not touch Hostinger, `etamen.inolty.com`, SSH, staging deployment, public launch, or real-phone readiness.

## Sprint 47 Blocker

Sprint 47 ended as:

```text
LOCAL_FITNESS_PAYMENT_UI_BLOCKED
```

Root blockers:

- The full Flutter emulator path for Gym booking -> payment method -> proof upload -> admin accept was not completed.
- The full Flutter emulator path for Coach booking -> payment method -> proof upload -> admin accept was not completed.
- A gym booking request sent by Flutter did not include the backend-required `provider_id`, so the API rejected it with validation error 422 before payment could be reached.

## Fixes Applied

Flutter:

- `CreateGymBookingRequest` now includes `provider_id`.
- `GymDetailsPage` passes the selected gym provider id when booking a membership plan or class.
- Payment status parsing now also reads nested `payment_method.type` as a safe fallback.
- Focused Flutter tests were updated for gym booking payload and payment method parsing.

No payment business rules were changed:

- Flutter still does not verify payment.
- Flutter still does not send trusted totals/statuses.
- Backend remains the source of truth for price, payment status, and booking status.

## Local Backend Prep

Local setup used:

```text
php artisan migrate:fresh --seed
php artisan db:seed --class=PilotDemoSeeder
php artisan etamen:ensure-payment-methods --staging
php artisan serve --host=0.0.0.0 --port=8000
```

Verified locally:

- `GET http://127.0.0.1:8000/api/v1/system/health` PASS.
- `GET /api/v1/gyms` returned the demo gym.
- `GET /api/v1/coaches` returned demo fitness/nutrition coaches.
- `GET /api/v1/payment-methods` returned active `manual_vodafone_cash` and `manual_instapay`.
- inactive Paymob was hidden from the public payment methods response.

## Gym E2E Result

Flutter emulator flow:

- Opened Services -> Gyms.
- Opened demo gym.
- Created a gym membership booking through Flutter.
- Reached payment methods page.
- Selected Vodafone Cash.
- Uploaded a real image from the emulator picker through the app.
- Payment moved to pending review.
- Local admin API accepted the same payment.
- Flutter refresh showed the gym booking as confirmed/paid.

Local records:

```text
gym_booking_id=1
gym_booking_number=GYM-20260510-JPCUDD7Y
payment_id=1
proof_id=1
final_payment_status=verified
final_gym_booking_status=confirmed
```

Admin accept evidence:

```text
I:\Etamen\.tmp\sprint48-local-fitness-e2e\gym\08-gym-admin-accept-response.json
```

## Coach E2E Result

Flutter emulator flow:

- Opened Services -> Coaches.
- Opened demo nutrition coach.
- Created a coach session booking through Flutter.
- Reached payment methods page.
- Selected Vodafone Cash.
- Uploaded a real image from the emulator picker through the app.
- Payment moved to pending review.
- Local admin API accepted the same payment.
- Flutter refresh showed the coach booking as confirmed/paid.

Local records:

```text
coach_booking_id=1
coach_booking_number=COACH-20260510-NNS9R4HO
payment_id=2
proof_id=2
final_payment_status=verified
final_coach_booking_status=confirmed
```

Admin accept evidence:

```text
I:\Etamen\.tmp\sprint48-local-fitness-e2e\coach\08-coach-admin-accept-response.json
```

## Security And Privacy Check

Patient-facing responses checked:

- `GET /api/v1/gym/bookings/1`
- `GET /api/v1/coach/bookings/1`
- `GET /api/v1/payments/1/status`
- `GET /api/v1/payments/2/status`
- `GET /api/v1/payment-methods`

Result:

- PASS: no `medical_private` disk name exposed.
- PASS: no `storage/private` path exposed.
- PASS: no raw `payment_proof/` path exposed.
- PASS: no payment config or Paymob secrets exposed.
- PASS: no private provider document fields exposed.

## Screenshots

Gym:

```text
I:\Etamen\.tmp\sprint48-local-fitness-e2e\gym\
```

Coach:

```text
I:\Etamen\.tmp\sprint48-local-fitness-e2e\coach\
```

## APK

Local emulator APK:

```text
I:\Etamen\.tmp\etamen-local-fitness-e2e.apk
```

Desktop copy:

```text
C:\Users\Ahmed Abdelkareem\OneDrive\Desktop\Etamen_Android_Website_Ready\etamen-local-fitness-e2e.apk
```

## Tests And Build

Completed during Sprint 48:

- backend `php artisan test` PASS after final updates: 244 tests, 1982 assertions.
- `flutter pub get` PASS.
- `dart format .` PASS.
- `flutter analyze` PASS.
- full `flutter test` PASS: 182 tests.
- local APK build PASS after pointing `GRADLE_USER_HOME` to the existing local Gradle cache on `C:` because `D:\gradle_home` had zero free space.

The final APK was copied to both required local paths.

## Remaining Blockers

Not approved by this sprint:

- staging readiness.
- Hostinger readiness.
- public launch readiness.
- real-phone gym/coach proof upload readiness.
- production fitness marketplace rollout.

## Decision

```text
LOCAL_FITNESS_PAYMENT_E2E_ACCEPTED
```

This decision proves only the local emulator Gym + Coach payment proof/admin accept path.
