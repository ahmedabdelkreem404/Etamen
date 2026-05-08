# Production And Scale Checklist

Date: 2026-05-08  
Sprint: 35 - Egypt-Scale Health Super App Architecture + Product Expansion Blueprint

This checklist is a readiness tool. It does not mean Etamen is production-ready today.

## Backend

- [ ] `APP_ENV=production`.
- [ ] `APP_DEBUG=false`.
- [ ] Secrets stored outside the repository.
- [ ] HTTPS enforced.
- [ ] Queue worker process manager configured.
- [ ] Scheduler configured.
- [ ] Horizon or queue dashboard considered/configured.
- [ ] Redis configured for cache, queues, and locks.
- [ ] Critical database indexes reviewed with real query plans.
- [ ] Object storage configured.
- [ ] Private file storage verified for medical files, payment proofs, and provider documents.
- [ ] Public storage limited to approved public assets like provider avatars/logos.
- [ ] Automated backups configured.
- [ ] Restore drill completed.
- [ ] Structured logs configured.
- [ ] Error tracking configured.
- [ ] API rate limits configured per endpoint class.
- [ ] Payment webhook HMAC validation verified.
- [ ] Payment webhook idempotency verified.
- [ ] File upload validation for type, size, and ownership.
- [ ] CORS restricted to approved app/web origins.
- [ ] Security headers configured.
- [ ] Audit logs enabled for sensitive admin actions.
- [ ] Health checks configured.
- [ ] Seed/demo data disabled or clearly separated from production.

## Flutter

- [ ] Release signing configured.
- [ ] Final app icon approved.
- [ ] Splash screen approved.
- [ ] Production API base URL configured.
- [ ] No localhost/10.0.2.2 in production builds.
- [ ] Crash reporting configured.
- [ ] Push notification token production flow configured.
- [ ] Deep links planned for later if not required in MVP.
- [ ] Arabic QA completed.
- [ ] English QA completed.
- [ ] RTL layout verified.
- [ ] Device matrix testing completed.
- [ ] Physical Android proof upload verified.
- [ ] Session restore/logout verified on physical Android.
- [ ] Performance profiling completed for core flows.
- [ ] Large list pagination verified.
- [ ] Bad-network and timeout UX verified.
- [ ] No raw backend statuses exposed to users.
- [ ] No private file paths exposed.

## Admin And Operations

- [ ] Admin roles and permissions reviewed.
- [ ] Payment review SOP written.
- [ ] Provider approval SOP written.
- [ ] Support SOP written.
- [ ] Refund SOP written.
- [ ] Incident SOP written.
- [ ] Legal documents reviewed.
- [ ] Medical disclaimers reviewed.
- [ ] Data retention policy defined.
- [ ] Content moderation process defined.
- [ ] Review moderation process defined.
- [ ] Provider document review process defined.
- [ ] Admin action audit reviewed.
- [ ] Escalation path for payment disputes.
- [ ] Escalation path for medical safety reports.

## Infrastructure

- [ ] Production domain.
- [ ] HTTPS certificate.
- [ ] Managed database or hardened database host.
- [ ] Database backups.
- [ ] Database restore drills.
- [ ] Redis.
- [ ] Queue workers.
- [ ] Object storage.
- [ ] CDN for public assets.
- [ ] Monitoring.
- [ ] Alerts.
- [ ] Uptime checks.
- [ ] Log aggregation.
- [ ] Error tracking.
- [ ] WAF/DDoS protection planned or configured.
- [ ] Load balancer planned for scale stage.
- [ ] Separate staging environment.
- [ ] Environment variable management.
- [ ] Deployment rollback process.

## Scale

- [ ] Load testing plan created.
- [ ] k6/JMeter scripts for core API flows.
- [ ] Login load test.
- [ ] Provider search load test.
- [ ] Doctor booking concurrency test.
- [ ] Payment webhook stress test.
- [ ] Payment proof upload stress test.
- [ ] File download stress test.
- [ ] Query profiling completed.
- [ ] Slow query log reviewed.
- [ ] Search benchmarking completed.
- [ ] Cache hit ratio measured.
- [ ] Queue throughput measured.
- [ ] Queue lag alert thresholds defined.
- [ ] Read replica strategy defined.
- [ ] Search engine strategy chosen.
- [ ] Archive strategy for high-volume logs.
- [ ] Incident response drill completed.

## Current Sprint 35 Status

Known not complete:
- Physical-device payment/admin gate is still pending from Sprint 33.
- Production infrastructure is not verified.
- Live payment operations are not verified.
- Load testing has not been performed.
- Search engine is not implemented.
- Egypt-scale capacity is not proven.

Recommended next gate:
- Complete physical Android proof upload, admin review, Flutter refresh state, and logout/session restore before inviting first supervised pilot users.

