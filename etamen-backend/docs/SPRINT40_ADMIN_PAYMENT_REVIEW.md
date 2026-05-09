# Sprint 40 Admin Payment Review Gate

Date: 2026-05-09

## Current Status

Admin payment review has not been completed in Sprint 40.

Reason:

- Staging `/api/v1/payment-methods` still returns an empty `data` array.
- The patient app cannot select Vodafone Cash or InstaPay.
- No real proof image has been uploaded from a physical phone.
- No payment exists in pending review state for this gate.

Status:

```text
PENDING_STAGING_PAYMENT_METHOD_ACTIVATION
```

## Required Before Admin Review

1. Deploy latest backend code to staging.
2. Run:

```text
php artisan etamen:ensure-payment-methods --staging
```

3. Confirm:

```text
GET https://etamen.inolty.com/api/v1/payment-methods
```

returns active `manual_vodafone_cash` and `manual_instapay`.

4. Product owner installs:

```text
C:\Users\Ahmed Abdelkareem\OneDrive\Desktop\Etamen_Android_Website_Ready\etamen-staging-payment-methods-proof-gate.apk
```

5. Product owner books the staging doctor and uploads a real test proof image from the phone.

## Admin Accept Test

After a real proof exists:

| Step | PASS/FAIL | Evidence | Notes |
| --- | --- | --- | --- |
| Login to admin panel |  |  |  |
| Locate the same payment |  |  |  |
| Confirm proof is visible to admin |  |  |  |
| Confirm patient does not see raw private path |  |  |  |
| Accept payment |  |  |  |
| Payment becomes verified |  |  |  |
| Appointment becomes confirmed |  |  |  |
| Invoice/audit behavior matches existing flow |  |  |  |
| Flutter refresh shows updated state |  |  |  |

## Optional Reject Test

| Step | PASS/FAIL | Evidence | Notes |
| --- | --- | --- | --- |
| Create second booking |  |  |  |
| Upload second proof |  |  |  |
| Reject with safe reason |  |  |  |
| Flutter shows friendly retry state |  |  |  |

## Hard Rules

- Do not verify payment from Flutter.
- Do not fake proof upload.
- Do not mark admin review PASS without reviewing the same uploaded proof.
- Do not expose private storage paths to patients.
- Do not claim public launch readiness from this gate.

## Sprint 40 Decision

```text
ADMIN_REVIEW_NOT_TESTED_PAYMENT_METHODS_STILL_BLOCKED
```
