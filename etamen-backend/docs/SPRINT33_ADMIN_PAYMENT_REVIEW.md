# Sprint 33 Admin Payment Review

Date: 2026-05-08

## Result

**NOT VERIFIED in Sprint 33 because no physical Android device was connected and no real proof upload was created from a phone.**

Backend automated coverage still passes for manual payment proof upload, admin accept, admin reject, and retry flows. However, Sprint 33 requires admin review of the **same physical-device uploaded proof**, and that could not be executed without a phone.

## Admin Surfaces

| Surface | Path |
| --- | --- |
| Filament admin login | `/admin/login` |
| Filament payments resource | `/admin/payments` |
| Filament payment proofs resource | `/admin/payment-proofs` |
| Admin API pending payments | `GET /api/v1/admin/payments/pending-review` |
| Admin API payment details | `GET /api/v1/admin/payments/{payment}` |
| Admin API accept manual payment | `POST /api/v1/admin/payments/{payment}/accept` |
| Admin API reject manual payment | `POST /api/v1/admin/payments/{payment}/reject` |

Local LAN base URL candidate for physical-device/admin testing:

- `http://192.168.1.5:8000/admin`
- `http://192.168.1.5:8000/api/v1`

## Required Manual Review Steps

1. Book an appointment from the physical Android device.
2. Select a manual payment method.
3. Upload a real local test image as proof from the phone.
4. Open the admin panel.
5. Locate the same pending payment.
6. Confirm the proof is visible/downloadable to admin only.
7. Accept the payment.
8. Confirm payment status becomes verified/paid according to the current backend contract.
9. Confirm linked appointment status changes as expected.
10. Reopen/refresh Flutter and confirm friendly patient copy.

Optional reject path:

1. Create another booking/payment.
2. Upload a second proof image.
3. Reject the proof with a safe reason.
4. Confirm Flutter shows a friendly retry state and no raw backend status.

## Current Verification State

| Check | Result | Notes |
| --- | --- | --- |
| Backend payment tests | PASS | Included in `php artisan test`: upload, accept, reject, retry, ownership, and frontend cannot verify. |
| Physical proof visible to admin | NOT TESTED | No phone upload exists. |
| Admin accepts same uploaded proof | NOT TESTED | Sprint 33 blocker. |
| Flutter sees verified/confirmed state after admin accept | NOT TESTED | Sprint 33 blocker. |
| Admin reject/retry path | NOT TESTED | Optional for first gate but still recommended. |

## Blocker

Pilot cannot be approved until at least the accept path is executed end-to-end on the same payment created from the physical Android device.
