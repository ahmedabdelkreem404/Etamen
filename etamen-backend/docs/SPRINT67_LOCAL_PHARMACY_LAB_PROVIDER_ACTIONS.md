# Sprint 67 Local Pharmacy/Lab Provider Actions

Sprint 67 is a local-only QA closeout on top of Sprint 66.

## Sprint 66 Closeout

Sprint 66 documentation was corrected from pending closeout to:

```text
LOCAL_PHARMACY_LAB_PATIENT_FLOWS_ACCEPTED
```

Evidence recorded:

- Backend: `265 tests / 2168 assertions`.
- Flutter: `196 tests`.
- APK: `I:/Etamen/.tmp/etamen-local-pharmacy-lab-hardening.apk`.
- Desktop APK: `C:/Users/Ahmed Abdelkareem/OneDrive/Desktop/Etamen_Android_Website_Ready/etamen-local-pharmacy-lab-hardening.apk`.
- Screenshots: `I:/Etamen/.tmp/sprint66-local-pharmacy-lab-hardening/`.
- Security: no raw prescription paths, no raw lab result paths, no secrets/payment config, no medical interpretation.

## Provider Pharmacy Actions

Workspace-scoped actions added:

- `POST /api/v1/provider/workspace/{provider}/pharmacy/orders/{order}/accept`
- `POST /api/v1/provider/workspace/{provider}/pharmacy/orders/{order}/reject`
- `POST /api/v1/provider/workspace/{provider}/pharmacy/orders/{order}/preparing`
- `POST /api/v1/provider/workspace/{provider}/pharmacy/orders/{order}/ready`
- `POST /api/v1/provider/workspace/{provider}/pharmacy/orders/{order}/out-for-delivery`
- `POST /api/v1/provider/workspace/{provider}/pharmacy/orders/{order}/complete`

Rules:

- `manage_pharmacy_orders` is required for actions.
- reject requires a reason.
- the backend owns status transitions.
- wrong-provider access is blocked.
- limited staff without manage permission is blocked.
- provider responses show prescription metadata only, not raw paths.

## Provider Lab Actions

Workspace-scoped actions added:

- `POST /api/v1/provider/workspace/{provider}/lab/orders/{order}/accept`
- `POST /api/v1/provider/workspace/{provider}/lab/orders/{order}/reject`
- `POST /api/v1/provider/workspace/{provider}/lab/orders/{order}/sample-scheduled`
- `POST /api/v1/provider/workspace/{provider}/lab/orders/{order}/sample-collected`
- `POST /api/v1/provider/workspace/{provider}/lab/orders/{order}/processing`
- `POST /api/v1/provider/workspace/{provider}/lab/orders/{order}/result-ready`
- `POST /api/v1/provider/workspace/{provider}/lab/orders/{order}/complete`

Rules:

- `manage_lab_orders` is required for actions.
- reject requires a reason.
- the backend owns status transitions.
- wrong-provider access is blocked.
- limited staff without manage permission is blocked.
- lab result metadata remains safe and does not include medical interpretation.

## Admin Payment Regression

Admin Operations payment review was checked for pharmacy/lab payment contexts:

- payment detail exposes proof metadata only.
- accept marks payment verified.
- linked pharmacy/lab order moves to paid.
- audit/payment services remain backend-owned.

## Security Sweep

Local Sprint 67 sweep target:

```text
I:/Etamen/.tmp/sprint67-local-pharmacy-lab-provider-actions/security-sweep.json
```

Must remain true:

- no raw prescription path.
- no raw lab result path.
- no secrets.
- no payment config.
- no private provider docs.
- provider cannot see another provider's pharmacy/lab order.
- limited staff cannot run unauthorized actions.
- admin sees proof metadata only.
- no diagnosis or medical interpretation.

Result:

```text
PASS
```

Checked endpoints:

- `GET /api/v1/provider/workspace/12/pharmacy/orders/1`
- `GET /api/v1/provider/workspace/14/lab/orders/2`
- `GET /api/v1/admin/operations/payments/4`
- `GET /api/v1/admin/operations/payments/5`
- `POST /api/v1/provider/workspace/12/pharmacy/orders/1/preparing` as limited staff
- `POST /api/v1/provider/workspace/14/lab/orders/2/processing` as limited staff
- wrong-provider pharmacy/lab detail endpoints

## Artifacts

- Screenshots: `I:/Etamen/.tmp/sprint67-local-pharmacy-lab-provider-actions/`.
- APK: `I:/Etamen/.tmp/etamen-local-pharmacy-lab-provider-actions.apk`.
- Desktop APK: `C:/Users/Ahmed Abdelkareem/OneDrive/Desktop/Etamen_Android_Website_Ready/etamen-local-pharmacy-lab-provider-actions.apk`.

Required screenshots captured:

- `01-provider-pharmacy-orders.png`
- `02-provider-pharmacy-order-details.png`
- `03-provider-pharmacy-accept.png`
- `04-provider-pharmacy-preparing-ready.png`
- `05-provider-pharmacy-reject-reason.png`
- `06-provider-lab-orders.png`
- `07-provider-lab-order-details.png`
- `08-provider-lab-accept.png`
- `09-provider-lab-processing-result-ready.png`
- `10-provider-lab-reject-reason.png`
- `11-admin-payment-review-pharmacy.png`
- `12-admin-payment-review-lab.png`
- `13-limited-staff-blocked-pharmacy-lab.png`

## Tests / Build

- Backend: `267 tests / 2269 assertions`.
- Flutter: `197 tests`.
- Flutter analyze: no issues.
- APK build: passed for `android-x64` local debug.
- `git diff --check`: passed.

## Decision

```text
LOCAL_PHARMACY_LAB_PROVIDER_ACTIONS_ACCEPTED
```

Sprint 67 is accepted locally because Sprint 66 docs were corrected, provider pharmacy/lab actions work through backend-owned workspace endpoints, admin payment regression works for pharmacy/lab contexts, limited staff and wrong-provider access are blocked, screenshots exist, tests/build pass, and the security sweep found no raw paths, secrets, payment config, or medical interpretation.
