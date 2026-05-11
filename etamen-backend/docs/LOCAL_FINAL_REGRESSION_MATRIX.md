# Local Final Regression Matrix

Sprint 61 locks Etamen for internal local demos only. This matrix summarizes the accepted local regression scope and explicitly blocks staging, production, public launch, app-store release, and external users.

## Patient Matrix

| Area | Local Result | Notes |
| --- | --- | --- |
| Login/session/logout | PASS | Normal auth flow, local QA buttons only with `ETAMEN_ENV=local`. |
| Doctor list/profile/slot booking/payment proof | PASS | Backend calculates price; Flutter never verifies payment. |
| My appointments | PASS | Payment/admin accept refresh is covered by local accepted flows. |
| Hospital section/context booking | PASS | Hospital context remains attached to appointment/payment path. |
| Pharmacy patient flow | PASS | Sprint 66 hardens catalog/order/prescription/manual payment/cancel-before-payment locally. |
| Lab patient flow | PASS | Sprint 66 hardens catalog/order/manual payment/result metadata/cancel-before-payment locally. |
| Radiology catalog/order/proof/result metadata/download | PASS | Result metadata is safe; raw paths blocked. |
| Health/vitals | PASS | Existing tests cover scoped health records and non-diagnostic copy. |
| Medications | PASS | Existing tests cover reminders, logs, and safe notifications. |
| Care plans/nutrition | PASS | Existing tests cover owner scope and non-medical safeguards. |
| Notifications | PASS | Local notification foundation remains safe; live FCM not approved. |
| AI assistant safety | PASS | Unsafe medical requests are blocked by safety tests. |
| Gym booking/payment proof | PASS | Local accepted flow remains covered. |
| Coach booking/payment proof | PASS | Local accepted flow remains covered. |
| Support ticket | PASS | Patient can create/view own tickets only. |
| Refund request | PASS | Foundation only; no live gateway refund. |
| Dispute | PASS | Patient-scoped foundation only. |

## Provider Matrix

| Area | Local Result | Notes |
| --- | --- | --- |
| Workspace switcher | PASS | Backend owns available workspaces. |
| Doctor dashboard/appointments | PASS | Own appointments only. |
| Hospital dashboard/appointments/departments/doctors | PASS | Hospital-context appointments only. |
| Radiology orders | PASS | Own orders only; no raw result path. |
| Pharmacy/lab operations | PASS | Sprint 66 confirms own-provider order views and safe status management with hidden raw paths. |
| Gym bookings/plans/classes | PASS | Own provider data only. |
| Coach bookings/availability/session types/packages | PASS | Own provider data only. |
| Provider support | PASS | Scoped provider tickets only. |
| Limited staff guard | PASS | Limited staff cannot perform unauthorized actions. |

## Admin Matrix

| Area | Local Result | Notes |
| --- | --- | --- |
| Workspace switcher | PASS | Platform admin workspace appears only for admin users. |
| Dashboard | PASS | Safe counts and quick actions. |
| Payment reviews/details/accept/reject | PASS | Proof metadata only; raw proof paths hidden. |
| Provider approvals/details/approve/reject/suspend | PASS | Document checklist metadata only. |
| Support tickets/internal notes | PASS | Internal notes are admin-only. |
| Refunds | PASS | Foundation only; manual decision/processing. |
| Disputes | PASS | Admin notes/audit events required by foundation. |
| Audit log | PASS | Safe summaries only. |

## Security Matrix

| Area | Local Result | Notes |
| --- | --- | --- |
| Non-admin blocked from admin | PASS | Patient/provider users receive 403. |
| Wrong-provider blocked | PASS | Provider staff cannot access another provider. |
| Raw proof/result/prescription paths | PASS | Security sweep checks no leaks. |
| Private provider documents | PASS | Public/provider/admin responses expose safe metadata only. |
| Secrets/payment config | PASS | No `.env`, APP_KEY, DB_PASSWORD, Paymob secret, or config leak in checked responses. |
| QA buttons local-only | PASS | `ETAMEN_ENV=local` only; unsafe fallback hides them. |

## Sprint 66 Pharmacy/Lab Addendum

Sprint 66 upgrades pharmacy/lab from smoke-focused demo modules to stronger local patient flows:

- pharmacy order creation, prescription metadata, manual payment proof path, own-order details, and cancel-before-payment.
- lab order creation, home/branch visit metadata, manual payment proof path, result metadata/download state, and cancel-before-payment.
- provider pharmacy/lab order visibility remains scoped to own provider.
- payment proof review remains admin-owned; Flutter never verifies payment.
- no diagnosis or interpretation is added to lab results.

## Not Approved

- Hostinger.
- `etamen.inolty.com`.
- Staging readiness.
- Production readiness.
- Public launch.
- App-store release.
- External users.
- Live Paymob.
- Live FCM.
- Legal/privacy/refund policy approval.
- Load testing.
- Server backup/restore or disaster recovery.
