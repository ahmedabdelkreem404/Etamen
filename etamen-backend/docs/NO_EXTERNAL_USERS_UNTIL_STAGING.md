# No External Users Until Staging

This is a hard scope lock for Etamen after Sprint 64.

## Rule

No external users are allowed until staging deployment, staging data recovery, security sweep, and real-phone staging QA are accepted.

## Not allowed before staging acceptance

- no real patients
- no real providers
- no real hospital staff
- no real pharmacy/lab/radiology/gym/coach operators
- no live payments
- no live refunds
- no production claims
- no public launch claims
- no app-store release
- no public ads
- no real medical data
- no external beta
- no use of live Paymob or live FCM

## Why

Healthcare software requires careful privacy, medical safety, legal, payment, support, and operational checks. The current accepted state is local demo only.

## What must happen first

1. Safe server access confirmed.
2. Backup current deployed code, database, and environment configuration.
3. Deploy latest main to staging.
4. Run safe migrations only.
5. Seed staging demo data safely.
6. Verify health/readiness/data/payment methods.
7. Run authenticated patient/provider/admin API QA.
8. Run security sweep.
9. Build staging APK.
10. Run real-phone staging QA.

Only after these gates pass can a supervised pilot discussion start. This still would not mean production/public launch.
