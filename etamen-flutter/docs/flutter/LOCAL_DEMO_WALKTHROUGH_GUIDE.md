# دليل العرض المحلي النهائي

هذا الدليل للعرض الداخلي المحلي فقط. لا يعني جاهزية staging أو production أو public launch أو app store، ولا يجب دعوة مستخدمين خارجيين بناءً عليه.

## 1. تشغيل الباك محليًا

```powershell
cd I:/Etamen/etamen-backend
php artisan migrate:fresh --seed
php artisan db:seed --class=PilotDemoSeeder
php artisan etamen:ensure-payment-methods --staging
php artisan serve --host=0.0.0.0 --port=8000
```

تأكد من:

```text
http://127.0.0.1:8000/api/v1/system/health
```

## 2. تشغيل APK على emulator

استخدم APK النهائي:

```text
I:/Etamen/.tmp/etamen-local-final-demo.apk
```

الـ API داخل APK:

```text
http://10.0.2.2:8000/api/v1
```

## 3. تسجيل دخول Patient QA

- افتح التطبيق.
- استخدم زر `Patient QA` لو build معمول بـ `ETAMEN_ENV=local`.
- أو سجل دخول يدويًا:

```text
p@b.co
Password1234
```

## 4. حجز دكتور ورفع إثبات

1. افتح Services.
2. افتح Doctors.
3. افتح طبيب تجريبي.
4. اختر slot.
5. أنشئ appointment.
6. اختر Vodafone Cash/InstaPay.
7. ارفع صورة إثبات دفع حقيقية من emulator.
8. الحالة تصبح في انتظار المراجعة.

## 5. قبول الدفع من Admin

1. سجل خروج من patient.
2. سجل دخول Admin QA:

```text
a@b.co
Password1234
```

3. افتح Platform Admin workspace.
4. افتح Payment Reviews.
5. افتح payment details.
6. اضغط Accept أو Reject مع سبب.
7. ارجع للـ patient وشاهد تحديث الحالة من الباك.

## 6. تجربة Radiology/Gym/Coach

- Radiology: اختر scan، أنشئ order، ارفع proof، ثم اعرض result metadata إن وجدت.
- Gym: اختر membership أو class، أنشئ booking، ارفع proof.
- Coach: اختر session type/slot، أنشئ booking، ارفع proof.

Flutter لا يتحقق من الدفع ولا يثق في السعر أو الحالة.

## 7. فتح Provider Workspace

1. سجل دخول Provider QA:

```text
d@b.co
Password1234
```

2. افتح Account.
3. افتح Workspace switcher.
4. اختر provider workspace.
5. افتح dashboard والعمليات المتاحة.

## 8. تجربة Admin Operations

من Platform Admin workspace:

- Dashboard.
- Payment reviews.
- Provider approvals.
- Support tickets.
- Refunds.
- Disputes.
- Audit log.

## 9. إنشاء Support/Refund/Dispute

من patient account:

- أنشئ support ticket.
- أنشئ refund request.
- أنشئ dispute.

المسارات foundation فقط، ولا يوجد live refund gateway.

## 10. إظهار Non-Admin Blocked

- سجل دخول patient أو provider.
- تأكد أن Platform Admin workspace لا يظهر.
- أي محاولة API admin يجب أن ترجع `403`.

## 11. Local Only

هذا العرض:

- local فقط.
- ليس staging.
- ليس production.
- ليس public launch.
- ليس app-store ready.
- لا يستخدم live Paymob أو live FCM.
- لا يسمح بمستخدمين خارجيين.
