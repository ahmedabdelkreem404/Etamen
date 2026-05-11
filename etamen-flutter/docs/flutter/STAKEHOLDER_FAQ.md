# أسئلة أصحاب المصلحة

هذا المستند للعرض الداخلي المحلي فقط. لا يعني staging readiness أو production readiness.

## هل التطبيق جاهز للإطلاق؟

لا. جاهز لديمو داخلي محلي فقط، وليس public launch أو app-store release.

## هل يشتغل على موبايل حقيقي؟

نعم، تم قبول real Android phone gate محلي ضد LAN backend في Sprint 53. هذا لا يعني جاهزية staging أو production.

## هل فيه دفع حقيقي؟

لا. الموجود manual proof upload ثم admin accept/reject. لا يوجد live Paymob ولا refund gateway live.

## هل ينفع ندخل مستخدمين؟

لا. external users غير معتمدين. الحسابات الحالية demo/local only.

## هل فيه AI؟

يوجد نطاق AI/safety محلي، لكنه ليس للتشخيص ولا العلاج.

## هل AI يشخص؟

لا. ممنوع التشخيص، وصف العلاج، إيقاف الدواء، أو التعامل مع الطوارئ كبديل للطبيب.

## هل فيه دكاترة/مستشفيات/صيدليات/معامل/أشعة/جيم/كوتش؟

نعم محليا. doctor/radiology/gym/coach flows أقوى، وpharmacy/lab في نطاق MVP/smoke أو read-only في بعض العمليات.

## إيه اللي ناقص قبل التشغيل الحقيقي؟

staging deployment، real staging phone QA، legal/privacy/payment approvals، load/security testing، backup/restore، وتشغيل دعم حقيقي.

## إيه الفرق بين local و staging و production؟

local للعرض الداخلي على جهاز المطور. staging اختبار سيرفر قبل الإنتاج ولم يعتمد بعد. production للمستخدمين الحقيقيين ولم يعتمد.

## هل فيه لوحة أدمن؟

نعم محليا: dashboard، payment reviews، provider approvals، support، refunds، disputes، audit log.

## هل فيه مزودين وصلاحيات staff؟

نعم محليا. الصلاحيات من backend، وFlutter لا يخترع صلاحيات.

## هل فيه دعم واسترداد ونزاعات؟

نعم foundation محلي. لا توجد أموال تتحرك تلقائيا.
