# Sprint 42 - Local Hospital Public Section

Date: 2026-05-09

Scope: local-only implementation. No Hostinger deployment, no staging API, and no public launch claim.

## Backend APIs Added

Public patient-safe hospital endpoints were added under `/api/v1`:

| Endpoint | Purpose |
| --- | --- |
| `GET /hospitals` | List approved active hospitals only. |
| `GET /hospitals/{hospital}` | Show approved active hospital details. |
| `GET /hospitals/{hospital}/departments` | Show active hospital departments. |
| `GET /hospitals/{hospital}/doctors` | Show approved active doctors linked to the hospital. |
| `GET /hospitals/{hospital}/departments/{department}/doctors` | Show approved active doctors linked to one department. |

## Public Safety Rules

The public hospital API exposes only patient-safe information:

- Provider id, provider type, Arabic/English names.
- Public description.
- Public phone/WhatsApp when present.
- Primary branch address summary, city, area, district, latitude, longitude.
- Departments/doctors counts.
- Hospital capability flags: emergency, outpatient, inpatient, ICU, ambulance.
- Approved/verified display state.

The public API does not expose:

- National ID files.
- Provider private documents.
- Tax/commercial/bank documents.
- Admin notes.
- Internal contracts.
- Raw private storage paths.
- Pending, rejected, or suspended hospitals/doctors.

## Demo Data Added

`PilotDemoSeeder` now creates one approved local demo hospital:

| Field | Value |
| --- | --- |
| Arabic name | مستشفى اطمن التخصصي |
| English name | Etamen Specialty Hospital |
| Location | مدينة نصر - القاهرة |
| Address | شارع تجريبي، مدينة نصر، القاهرة |
| Latitude/longitude | `30.0561`, `31.3300` |
| Capabilities | طوارئ، عيادات خارجية، إقامة داخلية، عناية مركزة، إسعاف |

Departments:

- قلب وأوعية دموية
- عظام
- أطفال
- جلدية
- نساء وتوليد

Each department is linked to an approved active demo doctor with profile, fee, branch, schedule, and slots. All data is local/staging demo data only.

## Local API Verification

After local reset and seed:

```text
php artisan migrate:fresh --seed
php artisan db:seed --class=PilotDemoSeeder
php artisan etamen:ensure-payment-methods --staging
```

Local endpoint checks:

| Check | Result |
| --- | --- |
| `/api/v1/system/health` | PASS |
| `/api/v1/hospitals` | PASS, one approved hospital returned |
| `/api/v1/hospitals/{id}/departments` | PASS, five active departments returned |
| `/api/v1/hospitals/{id}/departments/{department}/doctors` | PASS, approved active doctors returned |
| `/api/v1/payment-methods` | PASS, Vodafone Cash and InstaPay active, Paymob inactive |

## Booking Context

Hospital discovery reuses the existing doctor booking and payment flow.

Current behavior:

- Patient discovers doctor through hospital department.
- Patient opens the existing doctor profile.
- Existing appointment slot and payment flow is used.
- Backend remains the source of truth for price/status.
- No new hospital-specific booking contract was introduced.

Future gap:

- If the product owner needs hospital-specific appointment reporting later, add optional backend-owned context fields for hospital/department discovery. This sprint intentionally does not trust frontend context for price/status.

## Tests

Sprint 42 added/updated tests for:

- Approved active hospital visibility.
- Hidden pending/rejected/suspended hospitals.
- Safe details response.
- Active-only departments.
- Approved-only department doctors.
- Hidden pending linked doctors.
- Invalid provider type blocked.
- No private document/path leakage.
- Hospital-discovered booking still uses backend doctor profile price.
- Pilot demo hospital seed integrity.

Full backend test result:

```text
php artisan test
Tests: 223 passed (1811 assertions)
```

## Decision

Backend hospital public section is locally accepted.
