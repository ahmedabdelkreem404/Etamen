# No External Users Until Staging

This lock applies to all Etamen demos after Sprint 64.

## Hard rule

Do not invite external users until staging passes.

## Not allowed

- no real patients
- no real providers
- no live payments
- no live refunds
- no production claims
- no app-store release
- no public ads
- no real medical data
- no external beta
- no real support obligations

## How to phrase the current status

Use this:

```text
Etamen is approved for internal/local demo only. Staging, production, public launch, app-store, external users, and live payments are not approved.
```

Do not say:

```text
جاهز للإطلاق
شغال production
نقدر ندخل مستخدمين حقيقيين
الدفع شغال حقيقي
```

## Required gate before external users

- server access recovered safely
- backup-first staging deploy
- readiness/data checks
- payment method checks
- provider/admin workspace QA
- security sweep
- staging APK
- real Android phone staging QA
- legal/privacy/support/payment approval

## Demo fallback

If asked to share APK outside the internal team, decline and explain that the current artifact is local-only and not approved for external beta.
