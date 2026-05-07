# Sprint 27 Pilot Seed Data Checklist

This checklist defines the minimum backend/admin data needed before inviting real pilot users or claiming a complete real-device walkthrough.

## Sprint 28 Update

Sprint 28 added a dedicated backend seeder:

```powershell
cd I:\Etamen\etamen-backend
php artisan db:seed --class=PilotDemoSeeder
```

The seeder is local/staging/testing only, guarded against production, idempotent, and contains no real payment credentials or real patient private data.

Demo login for local walkthrough:

- Patient email: `pilot.patient@example.test`
- Password: `Password1234`

Important after running `migrate:fresh`: clear the installed Flutter app data or reinstall the app, because old Sanctum/mobile tokens become invalid.

## Patient

- [x] Local demo patient exists: `pilot.patient@example.test`.
- [x] Test password documented for local QA: `Password1234`.
- [x] `/me` works after login.
- [x] Health profile endpoint responds.
- [x] Health profile is seeded for local/staging demo.
- [ ] Pilot patient profiles are prepared with realistic non-demo names/phones if needed.

## Doctors

- [x] At least one approved doctor is visible publicly.
- [x] Specialty exists: Cardiology / `قلب وأوعية دموية`.
- [x] Branch/location exists: Cairo / Nasr City.
- [x] Doctor schedule exists.
- [x] Available slots exist for the next 14 days.
- [x] Consultation fee is configured by backend/admin.
- [x] Doctor appears in Flutter doctors list.

Sprint 28 verification: `/api/v1/doctors` returned the demo doctor, and `/api/v1/doctors/{doctor}/slots` returned available slots. Flutter was fixed to use the backend `limit` query parameter for slots.

## Payments

- [x] `manual_vodafone_cash` method is active.
- [x] `manual_instapay` method is active.
- [x] Arabic and English payment instructions are filled with fake local-only instructions.
- [ ] Proof upload endpoint accepts image proof in full walkthrough.
- [ ] Admin payment review path is known and staffed.
- [ ] Test pending payment appointment exists after booking during walkthrough.
- [x] Paymob mode is known: left inactive unless real sandbox config exists.

Sprint 28 verification: `/api/v1/payment-methods` returned two active manual methods.

## Pharmacy

- [x] Approved pharmacy exists.
- [x] Active product without prescription exists: `Panadol Demo`.
- [x] Active product requiring prescription exists: `Prescription Demo Medicine`.
- [ ] Stock/order acceptance path is known.
- [ ] Pharmacy order review path is known.
- [ ] Pharmacy payment path is known.

Sprint 28 verification: `/api/v1/pharmacies` returned the demo pharmacy and `/api/v1/pharmacies/{pharmacy}/products` returned two products.

## Labs

- [x] Approved lab exists.
- [x] Active lab tests exist: `CBC Demo` and `Blood Sugar Demo`.
- [x] Package exists: `Basic Checkup Demo`.
- [ ] Branch visit order can be created.
- [ ] Home collection order can be created if supported.
- [ ] Admin/provider result upload path is known.
- [x] Result download endpoint has a seeded demo result file.

Sprint 28 verification: `/api/v1/labs`, `/api/v1/labs/{lab}/tests`, and `/api/v1/labs/{lab}/packages` returned demo data.

## Health / Medications

- [x] Health profile endpoint responds.
- [x] Test vitals records exist.
- [x] Medication reminder exists.
- [x] Today medications can show schedule items.
- [ ] Taken/skipped logs can be created.

Sprint 28 verification: latest vitals and today medications returned seeded demo data.

## Care Plans

- [x] Active care plan assigned to the test patient, or patient-created active plan exists.
- [x] Plan has meals/instructions/foods if testing full details.
- [ ] Check-in and meal-log path can be tested.
- [ ] Progress endpoint has data after logging.

Sprint 28 verification: `/api/v1/care-plans` returned the demo nutrition follow-up plan.

## Notifications

- [x] At least one notification can be seeded or generated.
- [ ] Unread count changes when marking read.
- [ ] Preferences can be loaded and saved.
- [ ] Local token registration mode is known.

Sprint 28 verification: `/api/v1/notifications` returned one safe demo welcome notification.

## AI

- [ ] AI provider mode is known: fake/local, unavailable, or real backend-configured.
- [ ] Safe prompt path is known.
- [ ] Refusal prompt path is known.
- [ ] Red-flag prompt path is known.
- [ ] Rate limit behavior can be triggered safely.

## Minimum Before First 20 Pilot Users

- [ ] One complete doctor booking flow passes on emulator/real device after manual credential entry.
- [ ] One manual payment proof upload and admin verification passes.
- [ ] My Appointments reflects confirmed appointment after payment.
- [ ] One pharmacy order flow passes or is explicitly deferred from pilot.
- [ ] One lab order flow passes or is explicitly deferred from pilot.
- [ ] Logout/session restore pass on a physical Android device.
- [ ] Support contact and legal draft review are signed off for pilot wording.
