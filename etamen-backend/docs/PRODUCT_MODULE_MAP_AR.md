# خريطة وحدات المنتج

هذه الخريطة تلخص نضج وحدات Etamen في الديمو المحلي. لا تعني جاهزية production.

| Module | Local status | Maturity | Risks / blockers |
| --- | --- | --- | --- |
| Patient App | يعمل محليا للحجز والمتابعة والدعم | accepted local | يحتاج staging/production hardening |
| Provider Workspace | workspace switcher وdashboards محلية | accepted local | full provider portal غير مكتمل |
| Platform Admin | Operations Center محلي | accepted local | لا يستبدل Filament بالكامل |
| Doctors | booking/payment proof/admin accept محليا | accepted local | يحتاج real staging payment/admin QA |
| Hospitals | discovery/context booking محليا | accepted local | onboarding وتشغيل مستشفيات حقيقي غير جاهز |
| Radiology | catalog/order/proof/result metadata محليا | accepted local | result workflow الحقيقي يحتاج staging/storage QA |
| Pharmacy | catalog/order/prescription/manual proof flow local | accepted local | needs broader real operating policy before external use |
| Labs | catalog/order/manual proof/result metadata local | accepted local | no medical interpretation; needs broader real operating policy before external use |
| Gym | booking/payment proof محليا | accepted local | يحتاج provider workflow production hardening |
| Coaches | booking/payment proof محليا | accepted local | nutrition claims يجب ضبطها قانونيا وطبيا |
| Health/Vitals | health foundation محلي | MVP local | لا يقدم تشخيصا، يحتاج medical review |
| Medications | reminders foundation محلي | MVP local | لا يوقف/يغير علاج، يحتاج safety review |
| Care Plans | nutrition/care plans foundation | MVP local | لا يمثل prescription طبي |
| AI Assistant | safety guarded محليا | foundation only | يحتاج monitoring وسياسات قبل أي pilot |
| Notifications | local/in-app foundation | foundation only | live FCM غير معتمد |
| Support | tickets foundation محلي | accepted local | SOP وتشغيل support حقيقي غير جاهز |
| Refunds | manual foundation محلي | foundation only | لا يوجد live refund gateway |
| Disputes | dispute workflow foundation | accepted local | يحتاج escalation/legal SOP |
| Audit Log | safe audit summaries محلية | accepted local | يحتاج retention/export policies |
| Payments | manual proof flow محلي | accepted local | لا يوجد live Paymob أو reconciliation |

## الخلاصة

الديمو المحلي قوي لشرح النموذج وقيمة المنتج، لكنه ليس launch-ready. كل وحدة تحتاج staging validation قبل أي استخدام خارجي.

## Sprint 66 Local Update

Pharmacy and Labs are no longer smoke-only in the local demo. Sprint 66 adds local patient flow hardening, safer payment-status presentation in Flutter, patient cancel-before-payment, and deeper local seed catalogs. No live payment, medical interpretation, or external-user approval is implied.

## Sprint 67 Local Update

Pharmacy and Labs now have stronger local provider operation actions on the unified workspace path. Pharmacy providers can accept/reject with reason and move paid orders through preparing, ready, out_for_delivery, and complete. Lab providers can accept/reject with reason and move paid orders through sample_scheduled, sample_collected, processing, result_ready, and complete. Backend permission checks still own all authorization, limited staff is blocked from manage actions, wrong-provider access is blocked, and raw prescription/lab-result paths remain hidden.

## Sprint 68 Local Update

Pharmacy and Labs now have stronger local order-history UX for patient, provider, and admin visibility:

- patient history filters and status chips are accepted locally.
- provider history filters and action panels remain backend-permission guarded.
- admin payment review shows pharmacy/lab context labels safely.
- seed data covers multiple lifecycle states for local demos.
- lab result display remains metadata-only and does not interpret results medically.
- raw prescription paths, raw result paths, payment config, and secrets remain hidden.

Decision: `LOCAL_PHARMACY_LAB_HISTORY_POLISH_ACCEPTED`.

This remains local-only and does not approve staging, production, public launch, app-store release, external users, live payments, live refunds, or medical interpretation.

## Sprint 69 Local Update

Pharmacy and Labs now have stronger local catalog discovery UX:

- pharmacy catalog search, filters, sorting, stock labels, prescription badges, and selected-items summary are accepted locally.
- lab test/package search, filters, sorting by price/result time, sample/result-time metadata, collapsed preparation instructions, and selected-items summary are accepted locally.
- provider pharmacy/lab catalog lists have search/filter/sort visibility for own catalog only.
- seed data includes broader pharmacy product and lab test/package variety.
- inactive/private patient visibility rules are enforced by backend tests.
- no raw prescription paths, raw lab result paths, payment config, secrets, private provider docs, or medical interpretation are exposed.

Decision: `LOCAL_PHARMACY_LAB_CATALOG_POLISH_ACCEPTED`.

This remains local-only and does not approve production readiness, public launch, app-store release, external users, live payments, live refunds, or medical interpretation.
