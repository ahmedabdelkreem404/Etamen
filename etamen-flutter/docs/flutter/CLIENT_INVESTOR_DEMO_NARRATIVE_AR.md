# سرد عرض العميل والمستثمر

هذا المستند يشرح Etamen في سياق local demo فقط. لا تستخدمه كوعد إطلاق أو جاهزية production.

## المشكلة

المريض يحتاج رحلة صحية متصلة، بينما الخدمات غالبا متفرقة بين حجز، دفع، نتائج، ودعم. المزود أيضا يحتاج لوحة تشغيل مرتبطة بصلاحياته، والمنصة تحتاج عمليات مراجعة آمنة.

## Etamen solution

Etamen يجمع محليا:

- Patient app للحجز والمتابعة.
- Provider workspace حسب نوع المزود.
- Platform Admin Operations Center.
- Support/refund/dispute foundation.
- Safety and privacy guardrails.

## لماذا super-app model؟

الصحة رحلة متعددة الخطوات. super-app يسمح بتجربة واحدة تبدأ من service discovery وتصل إلى payment proof، provider operations، وadmin review.

## Patient value

- Services موحدة.
- Doctor booking.
- Radiology/Gym/Coach demo flows.
- Manual proof upload.
- Support/refund/dispute من الحساب.
- رسائل سلامة طبية واضحة.

## Provider value

- Workspace switcher.
- Dashboard.
- Operations MVP.
- Staff permissions من backend.
- منع wrong-provider access.

## Admin/platform value

- Payment review queue.
- Provider approvals.
- Support tickets.
- Refunds/disputes.
- Audit log.
- حظر non-admin access.

## Revenue possibilities

قد تكون هناك لاحقا عمولات حجوزات أو اشتراكات مزودين أو باقات مؤسسية. هذه احتمالات مستقبلية وليست revenue live أو claim تجاري حالي.

## Current local demo status

مقبول محليا فقط:

- Sprint 61 final demo package.
- Sprint 62 internal rehearsal package.
- Sprint 63 client/investor demo polish.

## What is not ready

- لا production.
- لا staging approval.
- لا public launch.
- لا app-store.
- لا external users.
- لا live payments أو live refunds.

## Next technical gate

server access + backup-first staging deployment + readiness/data verification + staging real-phone QA.
