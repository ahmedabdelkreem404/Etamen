# Internal Demo QA Checklist

استخدم هذه القائمة قبل أي عرض داخلي محلي. لا تستخدمها كقائمة إطلاق.

## Backend

- [ ] شغل backend من `I:/Etamen/etamen-backend`.
- [ ] شغل `php artisan migrate:fresh --seed` محليا فقط.
- [ ] شغل `php artisan db:seed --class=PilotDemoSeeder`.
- [ ] شغل `php artisan etamen:ensure-payment-methods --staging` لتفعيل manual methods محليا.
- [ ] تأكد من `http://127.0.0.1:8000/api/v1/system/health`.
- [ ] لا تفتح Hostinger أو staging أثناء العرض.

## Flutter APK

- [ ] استخدم APK محلي مبني على `ETAMEN_ENV=local`.
- [ ] تأكد أن API base هو `http://10.0.2.2:8000/api/v1` على emulator.
- [ ] ثبت APK قبل العرض.
- [ ] افتح التطبيق مرة للتأكد من عدم وجود session قديم مربك.

## Demo accounts

- [ ] اختبر patient account.
- [ ] اختبر provider owner account.
- [ ] اختبر platform admin account.
- [ ] اختبر limited staff إن كان ضمن العرض.
- [ ] لا تعرض الحسابات كأنها production credentials.

## Payment proof

- [ ] جهز صورة proof محلية في emulator/device.
- [ ] تأكد أن Vodafone Cash وInstaPay ظاهرين.
- [ ] لا تقل إن الدفع live أو automatic.

## Screenshots and fallback

- [ ] افتح مجلد screenshots:

```text
I:/Etamen/.tmp/sprint62-local-demo-rehearsal/
```

- [ ] جهز screenshots fallback لو حصل بطء أثناء الديمو.
- [ ] لا تعرض raw API أو debug traces لجمهور غير تقني.

## Scope lock to say out loud

- [ ] هذا internal local demo فقط.
- [ ] ليس staging.
- [ ] ليس production.
- [ ] ليس public launch.
- [ ] لا يوجد external users.
- [ ] لا يوجد live Paymob أو live refund gateway.

## Stop conditions

أوقف العرض أو انتقل للقطات جاهزة لو ظهر:

- API server غير متاح.
- login لا يعمل.
- proof picker عالق.
- أي error يعرض stack trace.
- أي شاشة تعرض path خاص أو secret.
