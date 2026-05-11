# Local Demo Fallback Plan

هذه الخطة للعرض المحلي فقط. لا تستخدم كخطة production incident.

## APK path

```text
I:/Etamen/.tmp/etamen-local-client-demo-polish.apk
C:/Users/Ahmed Abdelkareem/OneDrive/Desktop/Etamen_Android_Website_Ready/etamen-local-client-demo-polish.apk
```

## Screenshot pack

```text
I:/Etamen/.tmp/sprint63-local-client-demo-polish/
```

## Known demo accounts

راجع:

```text
etamen-flutter/docs/flutter/LOCAL_DEMO_ACCOUNTS.md
```

استخدم الحسابات المحلية المزيفة فقط. لا تعرضها كأنها production credentials.

## Fallback order

1. Live local app.
2. Pre-captured screenshots.
3. Docs walkthrough.
4. Stop and reschedule if privacy/security risk appears.

## If app freezes

- لا تفتح raw API لجمهور غير تقني.
- انتقل إلى screenshots.
- اشرح أن الديمو محلي، وليس production environment.
- لا تحاول إصلاح طويل أمام العميل.

## If payment proof picker fails

- اعرض screenshot شاشة proof upload.
- وضح أن flow تم قبوله محليا سابقا.
- أكد أن هذا ليس live payment.

## If login/session is stale

- استخدم logout أو clear app data.
- استخدم QA buttons فقط في `ETAMEN_ENV=local`.
- لا تضف أي bypass.

## Stop conditions

أوقف العرض لو ظهر:

- secret أو token.
- raw proof/result/private path.
- stack trace.
- بيانات مستخدم حقيقية.
- claim طبي غير آمن.
- طلب دعوة مستخدمين خارجيين قبل staging.

## What to avoid

- لا تعرض terminal logs لجمهور غير تقني.
- لا تعرض raw JSON إلا للشرح التقني الآمن.
- لا تذكر production readiness.
- لا تقل إن الدفع live.
- لا تعد بموعد إطلاق قبل server/staging gate.
