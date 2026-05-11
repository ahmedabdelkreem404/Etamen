# أسئلة أصحاب المصلحة

هذا المستند يشرح حالة Etamen الحالية بصدق للعرض الداخلي. لا يستخدم كتعهد إطلاق.

## هل التطبيق جاهز للإطلاق؟

لا. التطبيق جاهز لديمو داخلي محلي فقط. production وpublic launch وapp-store غير معتمدين.

## هل يشتغل على موبايل حقيقي؟

نعم محليا، Sprint 53 قبل real Android phone gate ضد backend محلي على LAN. هذا لا يعني أن staging أو production جاهزين.

## هل فيه دفع حقيقي؟

لا يوجد live payment. الموجود manual payment proof flow محلي: المستخدم يرفع إثبات، والأدمن يراجع ويقبل أو يرفض. لا يوجد live Paymob ولا تحويل أموال تلقائي.

## هل ينفع ندخل مستخدمين؟

لا. المستخدمون الخارجيون غير معتمدين. الديمو الداخلي فقط بحسابات seed محلية مزيفة.

## هل فيه AI؟

يوجد أساس AI/safety ضمن نطاق المنتج المحلي، لكنه ليس بديلا للطبيب ولا يستخدم للتشخيص أو وصف العلاج.

## هل AI يشخص؟

لا. AI لا يشخص، لا يصف علاج، لا يوقف دواء، ولا يتعامل مع الطوارئ. الحالات الخطرة يجب توجيهها لطبيب أو طوارئ.

## هل فيه دكاترة/مستشفيات/صيدليات/معامل/أشعة/جيم/كوتش؟

نعم في الديمو المحلي توجد وحدات patient-facing ومزودين لهذه المجالات بدرجات نضج مختلفة. pharmacy/lab مازالا MVP محافظ في أجزاء من التشغيل.

## إيه اللي ناقص قبل التشغيل الحقيقي؟

- staging deployment ناجح.
- readiness وdemo data على staging.
- real-phone staging gate.
- legal/privacy/refund/support policies.
- live payment/refund SOP.
- load/security testing.
- server backup/restore validation.
- support operations readiness.

## إيه الفرق بين local و staging و production؟

- local: تشغيل على جهاز المطور أو emulator/phone محلي، مقبول داخليا فقط.
- staging: بيئة سيرفر لاختبار ما قبل الإنتاج، غير معتمدة حاليا.
- production: تشغيل حقيقي لمستخدمين خارجيين، غير معتمد.

## هل فيه لوحة أدمن؟

نعم محليا: Platform Admin Operations Center للمراجعات والمدفوعات والدعم والاسترداد والنزاعات وaudit log.

## هل فيه مزودين وصلاحيات staff؟

نعم محليا: workspace switcher، provider dashboards، عمليات MVP، وصلاحيات staff يتحكم فيها backend.

## هل فيه دعم واسترداد ونزاعات؟

نعم foundation محلي. الاسترداد ليس gateway live. قرارات الأدمن مسجلة وmanual فقط.
