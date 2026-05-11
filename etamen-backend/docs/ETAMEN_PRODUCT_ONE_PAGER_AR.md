# Etamen Product One-Pager

## اسم المنتج

اطمن

## الجملة المختصرة

اطمن health super-app محلي للديمو الداخلي، بيجمع حجز الخدمات الصحية، متابعة المزودين، وتشغيل الأدمن في تجربة واحدة.

## التموضع

اطمن ليس مجرد booking app. الفكرة هي workspace واحد للمريض، المزود، وإدارة المنصة، مع manual payment proof، عمليات دعم، وصلاحيات staff محفوظة في backend.

## المستخدمون المستهدفون

- المريض: يحجز، يدفع بإثبات، يتابع الطلبات، ويرفع support/refund/dispute.
- المزود: طبيب، مستشفى، مركز أشعة، صيدلية، معمل، جيم، كوتش.
- الأدمن: يراجع المدفوعات، يعتمد المزودين، يتابع الدعم والنزاعات والاسترداد.

## الوحدات الرئيسية

- Authentication/session.
- Doctors and hospital context booking.
- Radiology catalog/order/result metadata.
- Gym and coach booking/payment proof.
- Provider workspaces and operations MVP.
- Platform Admin Operations Center.
- Support, refunds, disputes, audit log.
- Medical/privacy/pilot SOPs.

## الحالة الحالية

مقبول محليا فقط:

- patient app.
- provider workspaces.
- admin operations.
- local real phone gate.
- local final demo package.

غير معتمد:

- staging.
- production.
- public launch.
- app-store release.
- external users.
- live Paymob/live refunds.

## ما هو demo-ready؟

- عرض داخلي محلي على emulator أو جهاز محلي ضد backend محلي.
- شرح product vision.
- إثبات patient/provider/admin flows.
- عرض support/refund/dispute foundation.
- عرض guardrails وعدم تسريب private paths.

## ما ليس launch-ready

- لا يوجد staging deployment مقبول.
- لا يوجد production readiness.
- لا يوجد live payment/refund gateway.
- لا يوجد legal/privacy approval نهائي.
- لا يوجد load testing أو disaster recovery.
- pharmacy/lab مازالا محافظين في بعض أجزاء التشغيل.

## next technical gate

استعادة server access ثم staging deployment آمن بعد backup، وتشغيل readiness/data/real-phone staging gate.

## نبرة العرض

اطمن منتج واعد ومتماسك محليا، لكن لا يجب تقديمه كمنتج جاهز للجمهور قبل إغلاق بوابات staging والقانون والدفع والتشغيل الحقيقي.
