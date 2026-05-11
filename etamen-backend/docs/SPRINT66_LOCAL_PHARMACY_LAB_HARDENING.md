# Sprint 66 Local Pharmacy + Lab Hardening

## Backend Result

Pharmacy and lab are now stronger local patient demo flows.

Implemented or confirmed:

- patient can list pharmacies/labs and catalogs.
- patient creates pharmacy/lab orders with backend-calculated totals.
- patient sees own pharmacy/lab orders only.
- provider sees own pharmacy/lab orders only.
- wrong provider is blocked.
- prescription and lab result responses expose metadata only.
- manual payment proof uses existing payment APIs.
- admin payment accept updates linked pharmacy/lab order status.
- patient can cancel unpaid pharmacy/lab orders before payment flow starts.
- patient cannot cancel after payment starts.

## Routes Added

- `POST /api/v1/pharmacy/orders/{order}/cancel`
- `POST /api/v1/lab/orders/{order}/cancel`

Both routes require auth and enforce owner scope in the service layer.

## Payment Proof Status

Pharmacy/lab payment proof is supported through the existing manual payment flow:

- order accepted by provider/admin.
- patient creates payment.
- patient selects manual payment method.
- patient uploads proof.
- status moves to admin review.
- admin accept marks payment verified and linked order paid.

No live gateway was added.

## Security Notes

- no raw prescription paths in patient/provider/admin resources.
- no raw lab result paths in patient/provider/admin resources.
- no payment config or secrets in responses.
- patient/provider scoping remains enforced.
- lab result UI/resources do not interpret medical results.

## Evidence

- Screenshots root: `I:/Etamen/.tmp/sprint66-local-pharmacy-lab-hardening/`
- APK: `I:/Etamen/.tmp/etamen-local-pharmacy-lab-hardening.apk`
- Desktop APK: `C:/Users/Ahmed Abdelkareem/OneDrive/Desktop/Etamen_Android_Website_Ready/etamen-local-pharmacy-lab-hardening.apk`
- Backend tests: `265 tests / 2168 assertions`.
- Flutter tests: `196 tests`.
- Security sweep: no raw prescription paths, no raw lab result paths, no secrets/payment config, and no medical interpretation.

## Decision

`LOCAL_PHARMACY_LAB_PATIENT_FLOWS_ACCEPTED`

Sprint 66 is closed out as a local-only patient flow hardening sprint. Sprint 67 adds deeper provider-side action QA on top of this accepted baseline.
