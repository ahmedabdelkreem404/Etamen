# Sprint 41 Local Payment E2E

Date: 2026-05-09

Scope: local emulator only.

This sprint intentionally ignored Hostinger, SSH, staging, and `etamen.inolty.com`.

## Local Backend Setup

Commands run locally against the desktop development database:

```text
php artisan migrate:fresh --seed
php artisan db:seed --class=PilotDemoSeeder
php artisan etamen:ensure-payment-methods --staging
```

Result:

- `migrate:fresh --seed`: PASS.
- `PilotDemoSeeder`: PASS.
- `etamen:ensure-payment-methods --staging`: PASS.

Payment method command output:

```text
manual_vodafone_cash: active
manual_instapay: active
paymob: inactive
```

Local backend server:

```text
php artisan serve --host=0.0.0.0 --port=8000
```

Local API checks:

| Endpoint | Result |
| --- | --- |
| `GET http://127.0.0.1:8000/api/v1/system/health` | PASS, HTTP 200 |
| `GET http://127.0.0.1:8000/api/v1/doctors` | PASS, 4 approved demo doctors |
| `GET http://127.0.0.1:8000/api/v1/payment-methods` | PASS, Vodafone Cash + InstaPay |

Public payment methods response did not expose `config`, secrets, or inactive Paymob.

## Local E2E Record

| Field | Value |
| --- | --- |
| Appointment id | `13` |
| Payment id | `1` |
| Payment proof id | `1` |
| Payment method | `manual_vodafone_cash` |
| Amount | `EGP 300.00` |
| Proof file id | `2` |

The proof was uploaded through the Flutter app running on the Android emulator, not through a direct API shortcut.

## Proof Upload State

After upload:

- payment status: `pending_review`
- appointment status: `pending_payment_review`
- proof status: `pending_review`

The app showed a friendly pending-review state after refresh.

## Security / Privacy Check

Local API response checks:

| Check | Result |
| --- | --- |
| Payment methods include `config` | PASS, not exposed |
| Payment methods include inactive Paymob | PASS, not exposed |
| Payment status exposes private path | PASS, no private path |
| Appointments expose private path | PASS, no private path |

No `medical_private`, `storage/private`, raw proof path, payment config, or Paymob secret was found in patient-facing responses.

## Tests

Backend:

```text
php artisan test
```

Result:

- PASS.
- `218 passed`.
- `1746 assertions`.

Note: backend tests were run after the E2E evidence was captured.

## Decision

Local backend/payment gate:

```text
LOCAL_PAYMENT_E2E_ACCEPTED
```

This proves the local emulator payment path only. It does not prove staging, public launch, or real phone readiness.
