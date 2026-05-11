# سكريبت العرض المحلي الداخلي

هذا السكريبت مخصص للعرض الداخلي المحلي فقط. لا يعني جاهزية staging أو production أو public launch أو app store، ولا يجوز استخدامه لدعوة مستخدمين خارجيين.

## الافتتاحية

"اطمن — كل صحتك في مكان واحد"

اطمن هو health super-app محلي للديمو الداخلي: المريض يحجز ويتابع، المزود يدير عملياته، والأدمن يراجع المدفوعات والدعم والنزاعات. العرض لا يساوي إطلاقا حقيقيا.

## Patient Flow Demo

1. افتح التطبيق.
2. سجل دخول بحساب المريض المحلي أو زر QA المحلي إن كان build محلي.
3. افتح Home ثم Services.
4. افتح Doctors.
5. افتح Doctor profile.
6. اختار slot.
7. أنشئ booking.
8. افتح Payment methods.
9. اختار Vodafone Cash أو InstaPay.
10. ارفع صورة proof حقيقية من emulator/device.
11. اعرض pending review.

قل بوضوح:

Flutter لا يؤكد الدفع ولا يغير الحالة بنفسه. كل حالة payment أو booking تأتي من backend.

## Radiology / Gym / Coach Quick Demo

اعرض بسرعة:

- Radiology: catalog، order، proof، result metadata أو safe empty state.
- Gym: gym details، membership booking، proof state.
- Coach: session type، availability، booking.

لا تذكر أي تفسير طبي لنتائج radiology أو lab.

## Support / Refund / Dispute

من Account:

1. افتح support tickets.
2. أنشئ ticket.
3. أنشئ refund request.
4. أنشئ dispute.

وضح أن refund هنا foundation فقط، ولا يوجد تحويل أموال live.

## Provider Flow Demo

1. سجل خروج من patient.
2. سجل دخول provider owner محلي.
3. افتح Account.
4. افتح workspace switcher.
5. اختار provider workspace.
6. اعرض dashboard.
7. افتح quick action مثل appointments/bookings/orders.
8. افتح details إن كانت موجودة.
9. اعرض أن limited staff لا يملك صلاحيات غير مصرح بها.

قل بوضوح:

الصلاحيات تأتي من backend. Flutter لا يخترع admin أو provider permissions.

## Admin Flow Demo

1. سجل دخول platform admin محلي.
2. افتح workspace switcher.
3. اختار Platform Admin.
4. اعرض dashboard.
5. افتح payment review queue.
6. افتح payment details.
7. اعرض provider approval queue.
8. افتح support tickets.
9. افتح refunds/disputes.
10. افتح audit log.

أكد أن الأدمن يرى metadata آمنة فقط، ولا تظهر raw proof paths أو private docs.

## Safety Note

قل أثناء العرض:

- لا يوجد استخدام للطوارئ.
- التطبيق لا يشخص ولا يصف علاج.
- AI/support لا يستبدل الطبيب.
- الدفع والاسترداد في الديمو يدوي ومحلي.
- لا يوجد production أو public launch approval.

## Closing

هذا الديمو يثبت local internal readiness فقط. staging مازال محجوبا حتى يتم استعادة server access ثم deployment آمن وreadiness/data gate.
