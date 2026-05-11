# التعامل مع الاعتراضات

هذا المستند يساعد الفريق على الرد بوضوح أثناء عرض Etamen المحلي.

## ليه مش جاهز production؟

لأن production يحتاج staging deployment ناجح، اختبارات real environment، مراجعة أمنية وقانونية، live payment/refund SOP، backup/restore، load testing، ودعم تشغيلي. الديمو الحالي مقبول محليا فقط.

## ليه مفيش live payment؟

لأن الديمو يثبت manual proof flow فقط. الدفع الحي يحتاج sandbox/live gateway setup، reconciliation، refund policy، ومراجعة قانونية وتشغيلية.

## ليه مش هنجيب مستخدمين دلوقتي؟

لأن إدخال مستخدمين حقيقيين يتطلب بيئة staging/production آمنة، سياسة خصوصية، دعم، حماية بيانات، ومراجعة طبية وقانونية. هذا غير معتمد حاليا.

## إيه اللي يمنع الإطلاق؟

- staging غير معتمد.
- لا توجد موافقات قانونية نهائية.
- لا live payment/refund.
- لا load/security testing كامل.
- لا backup/restore server validation.
- لا support operations جاهزة لمستخدمين حقيقيين.

## هل AI آمن؟

AI عليه guardrails محلية: لا تشخيص، لا وصف علاج، لا إيقاف دواء، ولا طوارئ. لكنه يحتاج مراجعة إضافية قبل أي استخدام حقيقي.

## هل بيانات المرضى آمنة؟

محليا توجد اختبارات تمنع raw private paths وتفصل صلاحيات patient/provider/admin. لكن الإنتاج يحتاج مراجعة security/privacy كاملة على بيئة server حقيقية.

## هل ينفع المستشفيات تستخدمه؟

النموذج المحلي يدعم hospital discovery/context/provider operations MVP. الاستخدام الحقيقي يحتاج onboarding، عقود، صلاحيات، تدريب، ودعم تشغيل على staging ثم production.

## هل النظام scalable؟

المعمارية توسعت محليا لوحدات متعددة، لكن scalability الفعلية تحتاج load testing، queue/worker checks، observability، database tuning، وdeployment architecture.

## إيه الناقص قبل pilot حقيقي؟

staging deployment، demo data آمن، real-phone staging gate، legal/privacy/payment approvals، support SOP جاهز، backup/restore، وخطة تشغيل محدودة.

## إيه المطلوب من السيرفر؟

استعادة access آمن، backup قبل أي migration، deploy latest main، migrate، seed staging demo data، readiness checks، security sweep، ثم staging APK real-phone QA.
