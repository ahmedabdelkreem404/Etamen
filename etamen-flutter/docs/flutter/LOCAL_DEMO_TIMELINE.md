# Local Demo Timeline

هذا الجدول مخصص لتنظيم عرض داخلي محلي فقط. لا يستخدم كدليل إطلاق أو staging readiness.

## 5-minute demo

الهدف: إظهار الصورة الكبيرة بسرعة.

1. 30 ثانية: الافتتاحية "اطمن — كل صحتك في مكان واحد".
2. 90 ثانية: patient home + services + doctor booking حتى payment methods.
3. 60 ثانية: radiology/gym/coach quick glance.
4. 60 ثانية: provider workspace + dashboard.
5. 60 ثانية: admin dashboard + payment review + audit log.
6. 30 ثانية: safety and scope lock.

Skip:

- تفاصيل support/refund/dispute.
- provider sub-pages الكثيرة.
- أي debug أو API logs.

## 10-minute demo

الهدف: إثبات patient/provider/admin بشكل متوازن.

1. دقيقة: opening + local-only scope.
2. 3 دقائق: patient booking/payment proof.
3. دقيقة: radiology result metadata أو safe state.
4. دقيقة: gym/coach booking overview.
5. دقيقتان: provider workspace + operations.
6. دقيقتان: admin operations، support/refund/dispute، audit log.

Skip if time is short:

- pharmacy/lab details.
- كل أنواع providers.
- شرح seed data.

## 20-minute demo

الهدف: rehearsal كامل للفريق الداخلي.

1. دقيقتان: product positioning and scope.
2. 5 دقائق: patient journeys.
3. 4 دقائق: provider operations.
4. 5 دقائق: admin operations center.
5. دقيقتان: support/refund/dispute.
6. دقيقة: security/privacy guardrails.
7. دقيقة: next gate and blockers.

## What to skip if time is short

- pharmacy/lab MVP لأنهم conservative/read-only في أجزاء من العمليات.
- كل provider types بالتفصيل.
- technical logs.
- seed commands.
- أي حديث عن staging deployment.

## What not to show to non-technical audience

- terminal commands الطويلة.
- raw API responses.
- database tables.
- local credentials على الشاشة لفترة طويلة.
- أي error/debug trace.
- أي ملفات خاصة أو proof/result raw paths.

## What to say if asked about production

الإجابة المختصرة:

لا، التطبيق ليس جاهزا للإطلاق العام. المقبول حاليا هو local internal demo فقط. قبل production نحتاج staging deployment ناجح، real staging phone gate، legal/privacy/payment approvals، load/security testing، وتشغيل دعم وعمليات حقيقية.

## What to say if asked about staging

staging غير معتمد حاليا. الخطوة التالية هي server access recovery ثم backup-first deployment وreadiness/data verification.
