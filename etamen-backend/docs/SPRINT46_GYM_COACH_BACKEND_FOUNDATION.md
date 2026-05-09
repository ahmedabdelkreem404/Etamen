# Sprint 46 - Local Gym + Fitness Coach Backend Foundation

Status: local backend foundation implemented. No Flutter gym/coach screens were added. Nothing was deployed to staging.

## Scope

Sprint 46 adds backend foundations for:

- gym discovery.
- gym membership plans.
- gym classes.
- gym booking/payment foundation.
- fitness coach discovery.
- nutrition coach discovery.
- coach session types.
- coach availability slots.
- coach packages.
- coach booking/payment foundation.
- provider-scoped management APIs.
- admin listing APIs and Filament resources.

It does not make gym/coach patient flows available in Flutter yet.

## Tables Added

Gym:

- `gym_membership_plans`
- `gym_classes`
- `gym_bookings`
- `gym_booking_status_histories`

Coach:

- `coach_session_types`
- `coach_availability_slots`
- `coach_bookings`
- `coach_booking_status_histories`
- `coach_packages`

## Statuses

Gym booking statuses:

- `pending_payment`
- `pending_payment_review`
- `paid`
- `confirmed`
- `active`
- `completed`
- `cancelled_by_user`
- `cancelled_by_provider`
- `rejected`

Coach booking statuses:

- `pending_payment`
- `pending_payment_review`
- `paid`
- `confirmed`
- `in_progress`
- `completed`
- `cancelled_by_user`
- `cancelled_by_coach`
- `rejected`

Coach availability statuses:

- `available`
- `booked`
- `blocked`

## Public APIs

Gym:

- `GET /api/v1/gyms`
- `GET /api/v1/gyms/{gym}`
- `GET /api/v1/gyms/{gym}/membership-plans`
- `GET /api/v1/gyms/{gym}/classes`

Coach:

- `GET /api/v1/coaches`
- `GET /api/v1/coaches/{coach}`
- `GET /api/v1/coaches/{coach}/session-types`
- `GET /api/v1/coaches/{coach}/availability`
- `GET /api/v1/coaches/{coach}/packages`

Public responses are filtered to approved active providers and active catalog records. They do not expose provider documents, private files, internal contracts, payment config, or raw storage paths.

## Patient Booking APIs

Gym:

- `GET /api/v1/gym/bookings`
- `POST /api/v1/gym/bookings`
- `GET /api/v1/gym/bookings/{booking}`
- `POST /api/v1/gym/bookings/{booking}/cancel`

Coach:

- `GET /api/v1/coach/bookings`
- `POST /api/v1/coach/bookings`
- `GET /api/v1/coach/bookings/{booking}`
- `POST /api/v1/coach/bookings/{booking}/cancel`

Rules:

- authenticated patient only.
- backend calculates price.
- patient cannot set total, payment id, or status.
- patient can view only own bookings.

## Payment Integration

Gym and coach bookings reuse the existing manual payment module:

- payment created after paid booking creation.
- patient selects active manual method.
- proof upload stores private proof file.
- booking moves to `pending_payment_review`.
- admin accept verifies payment.
- booking moves to `confirmed`.
- invoice is created.

Wallet settlement for gym/coach is intentionally not expanded yet. The current wallet poster safely ignores unsupported payable types until commission rules are intentionally designed.

## Provider APIs

Gym provider:

- manage own membership plans.
- manage own classes.
- list own bookings.

Coach provider:

- manage own session types.
- manage own availability.
- manage own packages.
- list own bookings.

Provider scoping rules:

- gym provider cannot manage coach data.
- coach provider cannot manage gym data.
- provider cannot manage another provider's records.
- suspended providers cannot manage catalog records.

## Admin / Filament

Admin APIs:

- `GET /api/v1/admin/gym-bookings`
- `GET /api/v1/admin/gym-bookings/{booking}`
- `GET /api/v1/admin/coach-bookings`
- `GET /api/v1/admin/coach-bookings/{booking}`

Filament resources added:

- `GymMembershipPlanResource`
- `GymClassResource`
- `GymBookingResource`
- `GymBookingStatusHistoryResource`
- `CoachSessionTypeResource`
- `CoachAvailabilitySlotResource`
- `CoachPackageResource`
- `CoachBookingResource`
- `CoachBookingStatusHistoryResource`

## Seed Data

`PilotDemoSeeder` now adds:

- approved demo gym: `جيم اطمن`.
- two gym membership plans.
- two gym classes.
- approved demo fitness coach: `كابتن أحمد التجريبي`.
- approved demo nutrition coach: `د. تغذية تجريبي`.
- coach session types.
- availability slots.
- coach packages.

All data is local/staging demo only and must not be treated as real medical, nutrition, or fitness advice.

## Security Notes

- No private provider documents are exposed.
- No raw file paths are exposed.
- Coach certification text is plain public-safe summary only, not private document access.
- Nutrition coach copy avoids diagnosis, treatment claims, or medical prescriptions.
- Flutter cannot verify payment.
- Frontend price/status is rejected or ignored.

## Tests

Added `tests/Feature/GymCoachBackendSprint46Test.php`.

Coverage includes:

- public gym and coach visibility.
- inactive/unapproved filtering.
- backend-owned price calculation.
- manual proof upload and admin accept.
- patient scoping.
- provider scoping.
- unavailable coach slot rejection.
- public response privacy checks.

Verification run locally:

- `php artisan migrate:fresh --seed` PASS after changing Sprint 46 start/end columns to `dateTime` for local MySQL compatibility.
- `php artisan db:seed --class=PilotDemoSeeder` PASS.
- `php artisan test` PASS: 244 tests, 1982 assertions.
- `git diff --check` PASS.

## Remaining Work

Next Sprint 47 should implement local Flutter Gym/Coach UI:

- Services entry.
- gym list/details.
- membership/class order.
- coach list/details.
- session booking.
- reuse payment method/proof upload UI.
- local emulator QA.

Still not ready:

- staging QA.
- real phone gym/coach QA.
- production launch.
- public marketplace exposure.
