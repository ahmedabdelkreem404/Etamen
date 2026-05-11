# Sprint 66 Pharmacy + Lab Local Audit

Sprint 66 is local-only hardening for the weakest patient demo flows: pharmacy and lab.

## Existing Backend Coverage

- Public pharmacy providers and product catalog already exist.
- Patient pharmacy order creation already calculates totals on the backend.
- Prescription upload already stores files privately and returns metadata only.
- Provider pharmacy order/product views already enforce provider scope.
- Public labs, tests, packages, and patient lab order creation already exist.
- Lab result files already use protected download and safe metadata resources.
- Manual payment services already support `PharmacyOrder` and `LabOrder`.
- Admin payment accept already updates linked pharmacy/lab order payment state.

## Gaps Found

- Patient pharmacy/lab cancel routes were missing even though statuses allowed pre-payment cancellation.
- Local seed data was thinner than doctor/radiology/gym/coach demo data.
- Flutter payment status parsing did not surface pharmacy/lab order/payment status.
- Flutter order details showed raw payment status strings.
- Lab download fallback messages had unfriendly copy.

## Sprint 66 Scope

- Add safe patient cancel routes before payment starts.
- Keep backend totals/status authoritative.
- Keep manual payment proof through existing payment foundation.
- Keep provider views scoped to own pharmacy/lab.
- Keep lab result and prescription files private.
- Improve local seed catalog depth.
- Update tests/docs for local demo evidence.

## Out Of Scope

- No live payment gateway.
- No live refund gateway.
- No medical interpretation of lab results.
- No external users.
- No deployment or staging work.
