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
