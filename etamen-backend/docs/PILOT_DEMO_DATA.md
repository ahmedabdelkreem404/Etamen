# Pilot Demo Data

Date: 2026-05-07

This document describes local/staging demo data only. It must not be used as production medical truth.

## Demo Accounts

Password for all demo accounts:

`Password1234`

Primary accounts:

- Patient: `pilot.patient@example.test`
- Admin: `pilot.admin@example.test`
- Doctor provider: `pilot.doctor@example.test`
- Pharmacy provider: `pilot.pharmacy@example.test`
- Lab provider: `pilot.lab@example.test`

Expanded demo accounts also use `.example.test` addresses and are created only for richer local/staging screenshots.

## Doctor Visual Data

Primary doctor:

- Arabic name: `د. أحمد التجريبي`
- Specialty: `قلب وأوعية دموية`
- Branch: `مدينة نصر - القاهرة`
- Fee: `EGP 300`
- Experience: `8`
- Avatar: `public/legacy-doctorfinder/demo-doctor-avatar-1.png`
- Reviews: visible demo rating summary from completed demo appointments

Additional visual doctors:

- `د. سارة التجريبية` / Dermatology demo
- `د. يوسف التجريبي` / Pediatrics demo
- `د. كريم التجريبي` / Orthopedics demo

## Demo Avatar Assets

Generated safe placeholder assets:

- `public/legacy-doctorfinder/demo-doctor-avatar-1.png`
- `public/legacy-doctorfinder/demo-doctor-avatar-2.png`
- `public/legacy-doctorfinder/demo-doctor-avatar-3.png`

These are generated medical silhouette placeholders. They are not real doctor/person photos and do not imply identity, credentials, or endorsement.

## Pharmacy / Lab Demo Data

Primary pharmacy:

- Arabic name: `صيدلية اطمن التجريبية`
- Products include non-prescription and prescription-required demo items.

Primary lab:

- Arabic name: `معمل اطمن التجريبي`
- Tests include CBC and other demo tests.
- A private demo lab result file is created under `medical_private` and remains private.

Expanded demo catalog adds extra pharmacies, labs, products, packages, and tests for visual screenshots.

## How To Seed

Local/staging only:

```bash
php artisan migrate:fresh --seed
php artisan db:seed --class=PilotDemoSeeder
```

If the local `.env` lacks `APP_KEY`, generate or provide a local key before seeding because encrypted config casts require it.

## Warning

- Do not run `PilotDemoSeeder` in production.
- Demo reviews are for UI rating-summary testing only.
- Demo avatars are placeholders, not real humans.
- Demo payment numbers/handles are not real payment instructions.
