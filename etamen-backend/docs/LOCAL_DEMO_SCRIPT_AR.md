# سكريبت العرض المحلي الداخلي

هذا السكريبت مخصص للعرض الداخلي المحلي فقط. لا يعني جاهزية staging أو production أو public launch أو app store، ولا يجوز استخدامه لدعوة مستخدمين خارجيين.

## الافتتاحية

"اطمن — كل صحتك في مكان واحد"

ابدأ العرض بجملة بسيطة:

اطمن هو نموذج super-app صحي بيجمع تجربة المريض، المزود، وإدارة المنصة في تطبيق واحد. العرض الحالي محلي فقط لإثبات تدفق المنتج داخليا، وليس إطلاقا عاما.

## قبل بداية العرض

- شغل backend محليا على `http://127.0.0.1:8000/api/v1`.
- تأكد أن APK مبني على `http://10.0.2.2:8000/api/v1`.
- استخدم حسابات الديمو المحلية فقط.
- لا تفتح staging أو Hostinger أثناء العرض.
- لا تعرض أي ملفات خاصة أو أسرار أو مسارات تخزين خام.

## 1. Patient Flow

هدف هذا الجزء: إظهار أن المريض يقدر يستخدم الخدمات الأساسية من نفس التطبيق.

1. افتح التطبيق.
2. سجل دخول بحساب المريض المحلي.
3. افتح الصفحة الرئيسية ثم Services.
4. افتح Doctors.
5. افتح طبيب تجريبي.
6. اختار slot.
7. أنشئ booking.
8. افتح صفحة الدفع.
9. اختار Vodafone Cash أو InstaPay.
10. ارفع صورة إثبات دفع محلية.
11. وضح أن Flutter لا يؤكد الدفع بنفسه، بل ينتظر مراجعة الأدمن.

جملة العرض:

النظام لا يثق في السعر أو الحالة القادمة من Flutter. الحساب وتغيير الحالة يتمان من backend فقط.

## Radiology / Gym / Coach Quick Demo

اعرض بسرعة:

- Radiology: كتالوج، order، payment proof، result metadata بدون raw path.
- Gym: خطط وحجوزات، payment proof، حالة بعد قبول الأدمن.
- Coach: session type، availability، booking، payment proof.

جملة مهمة:

النتائج الطبية تظهر metadata وتنزيل آمن فقط. التطبيق لا يفسر الأشعة أو التحاليل طبيا.

## Support / Refund / Dispute

اعرض من Account:

- إنشاء support ticket.
- إنشاء refund request.
- إنشاء dispute.

وضح:

- refund approved يعني قرار منصة فقط، وليس تحويل فلوس تلقائي.
- processed يعني تأكيد يدوي من الأدمن.
- الدعم لا يقدم تشخيص أو وصف علاج.

## 2. Provider Flow

هدف هذا الجزء: إظهار أن نفس التطبيق يدعم workspace للمزود بدون اختراع صلاحيات من Flutter.

1. سجل خروج.
2. سجل دخول بحساب provider owner محلي.
3. افتح Account.
4. افتح workspace switcher.
5. اختار provider workspace.
6. افتح provider dashboard.
7. افتح quick action مثل appointments أو bookings.
8. اعرض التفاصيل الآمنة فقط.
9. اعرض limited staff guard إن كان ضمن وقت العرض.

جملة العرض:

الـ backend هو مصدر الحقيقة للصلاحيات. Flutter يخفي أو يظهر واجهات لتحسين التجربة فقط، لكن أي منع حقيقي يتم من API.

## 3. Admin Flow

هدف هذا الجزء: إظهار Operations Center لإدارة الديمو المحلي.

1. سجل دخول بحساب platform admin المحلي.
2. افتح workspace switcher.
3. اختار Platform Admin.
4. افتح dashboard.
5. اعرض payment review queue.
6. افتح payment details.
7. وضح أن proof metadata فقط ظاهر، بدون raw proof path.
8. اعرض provider approval queue.
9. اعرض support ticket details.
10. اعرض refund/dispute details.
11. افتح audit log.

جملة العرض:

الأدمن يرى قوائم تشغيل آمنة ومراجعات، وليس أسرار أو ملفات خاصة أو مسارات تخزين خام.

## Safety Note

يجب قول هذا بوضوح أثناء العرض:

- التطبيق ليس للطوارئ.
- التطبيق لا يستبدل الطبيب.
- AI أو الدعم لا يشخص ولا يصف علاج.
- لا يوجد live payment أو live refund gateway في هذا الديمو.
- لا يوجد staging أو production approval.

## الخاتمة

النتيجة الحالية:

```text
LOCAL_INTERNAL_DEMO_REHEARSAL_ACCEPTED
```

عند اكتمال Sprint 62، يكون Etamen جاهزا لعرض داخلي محلي فقط. الخطوة الحقيقية التالية قبل أي مستخدم خارجي هي استعادة مسار السيرفر ثم staging deployment آمن مع backup ومراجعة readiness.
