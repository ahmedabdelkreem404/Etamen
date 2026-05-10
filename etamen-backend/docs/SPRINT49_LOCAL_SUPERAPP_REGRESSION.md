# Sprint 49 Local Super App Regression

Date: 2026-05-10

## Scope

Sprint 49 was a local regression and pilot-scope lock sprint only.

It did not touch Hostinger, `etamen.inolty.com`, SSH, staging deployment, or production configuration.

## Local Backend Reset

Commands run locally:

```bash
php artisan migrate:fresh --seed
php artisan db:seed --class=PilotDemoSeeder
php artisan etamen:ensure-payment-methods --staging
php artisan serve --host=0.0.0.0 --port=8000
```

Verified local endpoints:

- `GET /api/v1/system/health`: PASS
- `GET /api/v1/doctors`: PASS, demo doctors available.
- `GET /api/v1/hospitals`: PASS, demo hospital available.
- `GET /api/v1/radiology/scans`: PASS, demo scans available.
- `GET /api/v1/gyms`: PASS, demo gym available.
- `GET /api/v1/coaches`: PASS, demo coaches available.
- `GET /api/v1/payment-methods`: PASS, Vodafone Cash and InstaPay active, Paymob hidden.

## Regression Results

### Authentication / Session

PASS locally.

- Patient login worked.
- Home loaded.
- Services loaded.
- Account loaded.
- Logout worked.
- Reopening the app showed the logged-out login screen.

### Direct Doctor Booking / Payment

PASS locally.

- Direct doctor profile opened.
- Slot selection worked.
- Appointment booking created a backend appointment.
- Payment methods opened.
- Vodafone Cash was selected.
- A real emulator-picked proof image was uploaded through Flutter.
- Local admin accepted the same payment.
- Payment became `verified`.
- Appointment became `confirmed`.

### Hospital Context Booking

PASS locally.

- Hospital list loaded.
- Hospital details loaded.
- Department doctors loaded.
- Doctor profile showed hospital context.
- Booking reached payment methods.
- Backend stored validated context:
  - `hospital_provider_id`
  - `hospital_department_id`
  - `hospital_doctor_id`

Payment proof was not repeated for this path because the direct doctor payment proof/admin accept path passed in the same sprint.

### Radiology Order / Payment / Result

PASS locally.

- Radiology catalog loaded.
- Scan was selected.
- Radiology order was created.
- Payment methods opened.
- Vodafone Cash was selected.
- A real emulator-picked proof image was uploaded through Flutter.
- Local admin accepted the same payment.
- Payment became `verified`.
- Radiology order became `paid`.
- Admin uploaded a visible demo result.
- Patient saw safe result metadata.
- Result download action completed without exposing raw storage paths.

### Gym Booking / Payment

PASS locally.

- Demo gym loaded.
- Membership booking was created through Flutter.
- Payment methods opened.
- Vodafone Cash was selected.
- A real emulator-picked proof image was uploaded through Flutter.
- Local admin accepted the same payment.
- Payment became `verified`.
- Gym booking became `confirmed`.

### Coach Booking / Payment

PASS locally.

- Coach list loaded.
- Coach details loaded.
- Session booking was created through Flutter.
- Payment methods opened.
- Vodafone Cash was selected.
- A real emulator-picked proof image was uploaded through Flutter.
- Local admin accepted the same payment.
- Payment became `verified`.
- Coach booking became `confirmed`.

### Pharmacy / Lab Smoke

SMOKE PASS locally.

- Pharmacy list opened and loaded demo pharmacies.
- Lab list opened and loaded demo labs.

Full pharmacy/lab payment E2E was not re-run in this sprint.

## Security / Privacy Sweep

Patient-facing endpoints checked:

- `/api/v1/payment-methods`
- `/api/v1/doctors`
- `/api/v1/hospitals`
- `/api/v1/appointments`
- `/api/v1/appointments/16`
- `/api/v1/appointments/17`
- `/api/v1/radiology/orders/1`
- `/api/v1/radiology/orders/1/results`
- `/api/v1/gym/bookings/1`
- `/api/v1/coach/bookings/1`
- `/api/v1/pharmacies`
- `/api/v1/labs`

No occurrences were found for:

- `medical_private`
- `storage/private`
- raw proof paths
- raw result paths
- raw file paths
- payment config
- Paymob secrets
- provider private documents
- national ID documents
- tax/commercial/bank documents
- admin notes
- internal contract terms

## Evidence

Screenshots:

```text
I:\Etamen\.tmp\sprint49-local-superapp-regression\
```

Security sweep JSON:

```text
I:\Etamen\.tmp\sprint49-local-superapp-regression\security-sweep.json
```

APK:

```text
I:\Etamen\.tmp\etamen-local-superapp-regression.apk
C:\Users\Ahmed Abdelkareem\OneDrive\Desktop\Etamen_Android_Website_Ready\etamen-local-superapp-regression.apk
```

## Tests / Build

Backend:

- `php artisan test`: PASS, 244 tests.
- `git diff --check`: PASS.

Flutter:

- `flutter pub get`: PASS.
- `dart format .`: PASS, 0 files changed.
- `flutter analyze`: PASS.
- `flutter test`: PASS, 182 tests.
- Local debug APK build for `android-x64`: PASS.

## Not Approved

Sprint 49 does not approve:

- staging readiness
- Hostinger readiness
- real Android phone readiness
- public launch readiness
- live Paymob
- live FCM
- legal/refund/support SOP readiness
- load testing readiness
- app store release

## Decision

```text
LOCAL_SUPERAPP_REGRESSION_ACCEPTED
```

## Next Step

Move the proven local APK/backend behavior to a controlled staging gate only after staging access/data/readiness are fixed. Then repeat doctor payment proof plus admin review on a real Android phone.
