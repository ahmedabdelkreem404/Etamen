# Etamen Pilot Demo Data

This document describes the local/staging-only demo data created by:

```bash
php artisan db:seed --class=PilotDemoSeeder
```

## Safety Warnings

- Local/staging/testing only.
- Do not run in production.
- No production secrets are included.
- No real Paymob credentials are included.
- No real AI provider keys are included.
- No real patient private medical data is included.
- Payment instructions use fake demo numbers only.
- Demo lab result file is a plain test file and not medical data.

The seeder has an environment guard and throws if `APP_ENV=production`.

## Demo Accounts

All demo accounts use:

`Password1234`

| Role | Email | Purpose |
| --- | --- | --- |
| Patient | `pilot.patient@example.test` | Flutter patient walkthrough |
| Patient | `demo.patient.asmaa@example.test` | Extra local patient |
| Patient | `demo.patient.omar@example.test` | Extra local patient |
| Patient | `demo.patient.mona@example.test` | Extra local patient |
| Doctor | `pilot.doctor@example.test` | Demo doctor/provider owner |
| Doctor | `demo.doctor.derma@example.test` | Extra dermatology doctor |
| Doctor | `demo.doctor.pedia@example.test` | Extra pediatrics doctor |
| Doctor | `demo.doctor.ortho@example.test` | Extra orthopedics doctor |
| Pharmacy admin | `pilot.pharmacy@example.test` | Demo pharmacy owner |
| Pharmacy admin | `demo.pharmacy.nasr@example.test` | Extra pharmacy owner |
| Pharmacy admin | `demo.pharmacy.maadi@example.test` | Extra pharmacy owner |
| Lab admin | `pilot.lab@example.test` | Demo lab owner |
| Lab admin | `demo.lab.nasr@example.test` | Extra lab owner |
| Lab admin | `demo.lab.maadi@example.test` | Extra lab owner |
| Admin | `pilot.admin@example.test` or `PILOT_ADMIN_EMAIL` | Local admin review/testing |

## Data Created

### Locations

- Cairo / Nasr City demo location.

### Doctor Booking

- Specialty: Cardiology, with Arabic demo label in the app.
- Approved active doctor provider: Dr Ahmed Demo, with Arabic demo name in the app.
- Extra approved doctors:
  - Dermatology demo doctor.
  - Pediatrics demo doctor.
  - Orthopedics demo doctor.
- Doctor profile fee: 300 EGP.
- Experience: 8 years.
- Main branch: Nasr City.
- Schedule: 10:00 to 16:00, 30-minute slots.
- Appointment slots generated for the next 14 days for all demo doctors.

### Payments

Active manual methods:

- Vodafone Cash
  - Demo number: `01000000000`
  - Instructions clearly say not to send real money.
- InstaPay
  - Demo handle: `etamen.demo@instapay`
  - Instructions clearly say not to send real money.

Paymob remains inactive unless backend sandbox configuration is added separately.

### Pharmacy

- Approved active provider: Etamen Demo Pharmacy, with Arabic demo name in the app.
- Extra approved pharmacies:
  - Demo Pharmacy Nasr City.
  - Demo Pharmacy Maadi.
- Product without prescription:
  - Panadol Demo
  - 45 EGP
  - stock 50
- Product requiring prescription:
  - Prescription Demo Medicine
  - 120 EGP
  - stock 20
- Extra pharmacy products include normal products and prescription-required products.

### Labs

- Approved active provider: Etamen Demo Lab, with Arabic demo name in the app.
- Extra approved labs:
  - Demo Lab Nasr City.
  - Demo Lab Maadi.
- Tests:
  - CBC Demo, 180 EGP
  - Blood Sugar Demo, 90 EGP
- Extra tests include liver functions, kidney functions, lipid profile, TSH, and Vitamin D demo tests.
- Package:
  - Basic Checkup Demo, 240 EGP
- Extra lab checkup packages are created for the extra labs.
- Existing demo result order:
  - `LAB-PILOT-RESULT-001`
  - Visible demo lab result file for download testing.

### Health

For the pilot and extra demo patients:

- Health profile.
- Demo blood pressure record.
- Demo blood sugar record.
- Demo weight record.

### Medications

For the pilot and extra demo patients:

- Active reminder: Demo Medication.
- Times: 09:00 and 21:00.
- Reminder text says it is for organization only, not medical advice.

### Care Plans

For the pilot patient:

- Active nutrition care plan: Demo nutrition follow-up plan.
- One demo day.
- Breakfast, lunch, and dinner.
- Recommended, limited, and allowed food items.
- Safe instruction and safety disclaimer.

### Notifications

For the pilot and extra demo patients:

- In-app notification:
  - Title: Welcome to Etamen.
  - Body: Safe demo notification for testing in-app notifications.

## Current Expanded Demo Counts

After running `PilotDemoSeeder` on the local MySQL database, expected minimum counts are:

| Table/area | Expected count |
| --- | ---: |
| Users | 16 |
| Providers | 10 |
| Doctor profiles | 4 |
| Pharmacy products | 7 |
| Lab tests | 7 |
| Lab packages | 3 |
| Appointment slots | 672 |
| Health profiles | 4 |
| Vital records | 12 |
| Medication reminders | 4 |
| Care plans | 1 |
| Notifications | 4 |

### AI

The seeder does not create AI secrets or provider credentials. AI behavior depends on backend configuration:

- fake/local provider
- unavailable provider
- real provider configured only in backend environment

## How To Run From Clean Local Database

```bash
cd I:/Etamen/etamen-backend
php artisan migrate:fresh --seed
php artisan db:seed --class=PilotDemoSeeder
```

## How To Verify

Login as the patient:

```bash
POST /api/v1/auth/login
email=pilot.patient@example.test
password=Password1234
```

Then verify:

- `GET /api/v1/me`
- `GET /api/v1/doctors`
- `GET /api/v1/doctors/{doctor}/slots`
- `GET /api/v1/payment-methods`
- `GET /api/v1/pharmacies`
- `GET /api/v1/pharmacies/{pharmacy}/products`
- `GET /api/v1/labs`
- `GET /api/v1/labs/{lab}/tests`
- `GET /api/v1/labs/{lab}/packages`
- `GET /api/v1/health/profile`
- `GET /api/v1/health/vitals/latest`
- `GET /api/v1/medications/today`
- `GET /api/v1/care-plans`
- `GET /api/v1/notifications`

## Reset Notes

For a full local reset:

```bash
php artisan migrate:fresh --seed
php artisan db:seed --class=PilotDemoSeeder
```

The seeder is idempotent and can be rerun without duplicating the demo records.

After resetting the database, reinstall the Flutter app or clear app data because old mobile tokens become invalid.
