# Sprint 39 Admin Payment Review

Date: 2026-05-09

## Status

Admin payment review was not completed in Sprint 39.

Reason:

- Staging doctor booking now works and creates an appointment.
- The payment step opens.
- `/api/v1/payment-methods` returns an empty `data` array.
- Because no active payment method is available, the app cannot reach the real proof upload flow.
- Without an uploaded proof, there is no same-payment record to accept or reject in admin.

This was not faked.

## What Was Verified

| Item | Result |
| --- | --- |
| Staging doctor exists | PASS |
| Doctor slots exist | PASS |
| Booking creates appointment | PASS |
| Payment methods endpoint exists | PASS, HTTP 200 |
| Active payment methods returned | FAIL, empty list |
| Proof uploaded | NOT TESTED |
| Admin saw proof | NOT TESTED |
| Admin accepted/rejected payment | NOT TESTED |
| Patient app saw updated payment state | NOT TESTED |

## Required Server/Admin Setup

Before this review can be tested:

1. Restore SSH, SFTP, Hostinger File Manager, or another safe server access path.
2. Create or activate staging-safe manual payment methods:
   - Vodafone Cash.
   - InstaPay.
3. Use demo/test payment instructions only unless the owner explicitly approves real staging payment details.
4. Confirm:

```text
GET https://etamen.inolty.com/api/v1/payment-methods
```

returns at least one active manual method.

## Admin Review Steps To Run After Proof Exists

1. Login to the staging admin panel.
2. Open the payments/manual payments area.
3. Find the payment created by the product-owner phone test.
4. Confirm the uploaded proof is visible to admin only.
5. Accept the payment.
6. Confirm payment status changes to the expected accepted/verified state.
7. Confirm appointment status changes to the expected confirmed state.
8. Reopen or refresh the Flutter app.
9. Confirm the patient sees friendly confirmed/verified wording.

Optional reject path:

1. Create another booking and upload a new proof.
2. Reject the proof with a safe reason.
3. Confirm the patient sees friendly retry wording.
4. Confirm the patient can retry upload if that is supported by the current flow.

## Privacy Rules

- Do not expose proof file paths to patients.
- Do not expose private storage paths through API.
- Do not mark payment verified from Flutter.
- Do not bypass admin review.

## Sprint 39 Decision

Admin review status:

- `PENDING_PAYMENT_METHODS_AND_OWNER_PHONE_PROOF`

This remains a blocker before inviting supervised pilot users.
