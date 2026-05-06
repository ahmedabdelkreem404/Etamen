# Sprint 27 Pilot Seed Data Checklist

This checklist defines the minimum backend/admin data needed before inviting real pilot users or claiming a complete real-device walkthrough.

## Patient

- [x] Local test patient exists: `sprint26195627@example.com`.
- [x] Test password documented for local QA: `Password1234`.
- [x] `/me` works after login.
- [x] Health profile endpoint responds.
- [ ] Pilot patient profiles are prepared with realistic names/phones if needed.

## Doctors

- [ ] At least one approved doctor is visible publicly.
- [ ] Specialty exists.
- [ ] Branch/location exists.
- [ ] Doctor schedule exists.
- [ ] Available slots exist.
- [ ] Consultation fee is configured by backend/admin.
- [ ] Doctor appears in Flutter doctors list.

Current Sprint 27 finding: `/api/v1/doctors` returned an empty list.

## Payments

- [ ] `manual_vodafone_cash` method is active.
- [ ] `manual_instapay` method is active.
- [ ] Arabic and English payment instructions are filled.
- [ ] Proof upload endpoint accepts image proof.
- [ ] Admin payment review path is known and staffed.
- [ ] Test pending payment appointment exists.
- [ ] Paymob mode is known: configured, disabled, or unavailable.

Current Sprint 27 finding: `/api/v1/payment-methods` returned an empty list.

## Pharmacy

- [ ] Approved pharmacy exists.
- [ ] Active product without prescription exists.
- [ ] Active product requiring prescription exists.
- [ ] Stock/order acceptance path is known.
- [ ] Pharmacy order review path is known.
- [ ] Pharmacy payment path is known.

Current Sprint 27 finding: `/api/v1/pharmacies` returned an empty list.

## Labs

- [ ] Approved lab exists.
- [ ] Active lab test exists.
- [ ] Package exists if packages are part of pilot.
- [ ] Branch visit order can be created.
- [ ] Home collection order can be created if supported.
- [ ] Admin/provider result upload path is known.
- [ ] Result download endpoint has a test result.

Current Sprint 27 finding: `/api/v1/labs` returned an empty list.

## Health / Medications

- [x] Health profile endpoint responds.
- [ ] Test vitals records exist or can be created during QA.
- [ ] Medication reminder can be created.
- [ ] Today medications can show a schedule item.
- [ ] Taken/skipped logs can be created.

Current Sprint 27 finding: medications endpoint returned an empty list.

## Care Plans

- [ ] Active care plan assigned to the test patient, or patient-created active plan exists.
- [ ] Plan has meals/instructions/foods if testing full details.
- [ ] Check-in and meal-log path can be tested.
- [ ] Progress endpoint has data after logging.

Current Sprint 27 finding: `/api/v1/care-plans` returned an empty list.

## Notifications

- [ ] At least one notification can be seeded or generated.
- [ ] Unread count changes when marking read.
- [ ] Preferences can be loaded and saved.
- [ ] Local token registration mode is known.

Current Sprint 27 finding: notifications endpoint reachable, empty list.

## AI

- [ ] AI provider mode is known: fake/local, unavailable, or real backend-configured.
- [ ] Safe prompt path is known.
- [ ] Refusal prompt path is known.
- [ ] Red-flag prompt path is known.
- [ ] Rate limit behavior can be triggered safely.

## Minimum Before First 20 Pilot Users

- [ ] One complete doctor booking flow passes.
- [ ] One manual payment proof upload and admin verification passes.
- [ ] My Appointments reflects confirmed appointment after payment.
- [ ] One pharmacy order flow passes or is explicitly deferred from pilot.
- [ ] One lab order flow passes or is explicitly deferred from pilot.
- [ ] Logout/session restore pass on a physical Android device.
- [ ] Support contact and legal draft review are signed off for pilot wording.
