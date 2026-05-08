# Egypt-Scale Architecture Plan

Date: 2026-05-08  
Sprint: 35 - Egypt-Scale Health Super App Architecture + Product Expansion Blueprint

This document is a scale path, not a claim of current capacity.

The current Etamen project is not proven for 10M users or 1M providers. No load testing, production observability, managed infrastructure, queue throughput testing, database read-replica testing, or search benchmark has been completed yet.

## Target

Long-term target:
- 10M patient/users.
- 1M combined providers, doctors, clinics, hospitals, pharmacies, labs, radiology centers, gyms, coaches, and partners.
- High search and discovery traffic.
- Reliable appointment booking and payment review.
- Strict medical file privacy.
- Arabic-first experience with English support.

## Stage 1: Local MVP / Supervised Pilot

Current/near-current posture.

Components:
- Single Laravel app server.
- Single database.
- Local/staging seeded demo data.
- Manual operations.
- Manual payment proof review.
- Flutter patient app.
- Filament admin.
- No public launch.

Required before supervised pilot:
- Physical Android proof upload verified.
- Admin manual payment accept path verified against the same payment.
- Flutter refresh sees updated payment/appointment state.
- Logout/session restore verified on a physical Android device.
- Pilot scope defined, likely doctors + payments first if pharmacy/labs are not physically verified.

Known limits:
- No production-grade horizontal scaling.
- No production object storage/CDN verification.
- No live payment operations proof.
- No queue throughput proof.
- No public load test.
- No large provider search benchmark.

## Stage 2: Production MVP Egypt City-Level

Goal: make a safe city-level production MVP, not Egypt-wide scale.

Components:
- HTTPS production domain.
- Managed MySQL/PostgreSQL.
- Redis for cache, locks, and queues.
- Queue workers for notifications, uploads, payment callbacks, and heavy admin jobs.
- Object storage for medical files and payment proofs.
- Private file access through signed/backend-authorized routes.
- Backups and restore drills.
- Error tracking and structured logs.
- Monitoring and uptime checks.
- API rate limiting.
- Production Paymob configuration and webhook HMAC validation.
- FCM production credentials.
- Basic CDN for public static assets only.
- Release-signed Flutter app.

Exit criteria:
- Payment proof upload and admin review pass on real devices.
- Live payment flow is verified in sandbox/production-like mode.
- Backups restore successfully.
- Error tracking is live.
- Queue workers and scheduler are supervised.
- Admin operations SOPs are approved.

## Stage 3: Egypt-Wide Scaling

Goal: scale reads, search, queues, and files across many cities.

Components:
- Load balancer.
- Multiple stateless Laravel app nodes.
- Autoscaled queue workers by domain:
  - payments
  - notifications
  - file processing
  - search indexing
  - audit/events
- Redis cluster or managed Redis.
- Database read replicas for heavy read/reporting paths.
- Search engine:
  - Meilisearch for simpler product/provider search, or
  - OpenSearch/Elasticsearch for heavier geo/ranking/analytics needs.
- Object storage plus CDN for approved public assets.
- Separate private medical-file delivery path.
- API gateway or WAF.
- Analytics/event pipeline.
- Centralized logs and traces.
- Background jobs split by domain.

Exit criteria:
- Load test covers login, search, doctor listing, booking, payment proof upload, notifications, and admin review.
- Critical database queries are profiled and indexed.
- Search engine benchmark meets Arabic search and geo-radius requirements.
- Queue lag alerts are in place.
- Incident response process is rehearsed.

## Stage 4: Massive Scale

Goal: support national-scale load and operational complexity.

Possible architecture shifts:
- Database partitioning by domain/region if single primary DB becomes a limit.
- Provider search index fully separated from transactional DB.
- Event-driven architecture for payments, notifications, audit logs, and settlements.
- CQRS-style read models for heavy marketplace pages.
- Dedicated geo-search service if search engine geo features are not enough.
- Data warehouse for analytics, finance, growth, and operations reporting.
- Multi-region disaster recovery plan.
- SLO/SLA definitions.
- On-call and incident response.
- Fraud/risk scoring pipeline.

Exit criteria:
- Real production traffic and load testing show bottlenecks that justify these changes.
- Teams and operations can support distributed systems.
- Data privacy and audit obligations are preserved across services.

## Recommended Architecture Diagram

```text
Flutter App / Web Landing
        |
        v
API Gateway / WAF / Rate Limits
        |
        v
Laravel API Nodes  ----->  Redis Cache / Locks
        |                         |
        |                         v
        |                   Queue Workers
        |                         |
        v                         v
Primary Database  <---->  Read Replicas
        |
        +----> Search Indexer ----> Meilisearch/OpenSearch
        |
        +----> Object Storage ----> CDN for public assets
        |
        +----> Private File Service for medical/payment files
        |
        +----> Monitoring / Logs / Audit / Alerts
```

## Request Flow

1. Client sends authenticated API request.
2. Gateway/WAF applies basic protection and rate limits.
3. Laravel validates token, authorization, and request DTO.
4. Transactional data is read/written in the primary database.
5. Slow side effects are dispatched to queues.
6. Search indexes, notifications, audit logs, and analytics are updated asynchronously.
7. Response returns bounded JSON payloads with no private paths.

## Appointment Booking Transaction Flow

1. Patient selects provider, branch, schedule, and slot.
2. API validates:
   - patient identity
   - slot availability
   - provider active/approved status
   - branch/schedule consistency
3. Booking occurs inside a database transaction.
4. Slot capacity is locked/updated safely.
5. Appointment status is created.
6. Payment record is created when required.
7. Notification jobs are queued.
8. Audit entry is recorded.

Scale notes:
- Use database row locks or atomic slot counters.
- Avoid frontend-trusted fee/status.
- Add idempotency keys for retries.
- Add unique constraints to prevent duplicate slot booking.

## Payment Flow

Manual payment:
1. Patient selects method.
2. Patient uploads proof.
3. File is stored privately.
4. Payment proof is linked to payment.
5. Admin reviews proof.
6. Admin accept/reject changes payment state.
7. Appointment state updates according to backend rules.
8. Patient sees friendly status only.

Paymob:
1. Backend creates payment attempt.
2. Paymob redirects/returns result.
3. Webhook is verified using HMAC.
4. Backend updates payment from trusted provider callback.
5. Wallet/commission entries are created only from backend-confirmed payment.

Scale notes:
- Webhooks need idempotency.
- Payment status changes need audit logs.
- Flutter must never verify payment.
- Refunds and settlements need separate state machines.

## Search Flow

Stage 1-2:
1. API uses indexed database filters.
2. Search is paginated and bounded.
3. Location fallback uses city/area if user denies permission.

Stage 3+:
1. Search request goes to search engine.
2. Search engine returns provider/service IDs.
3. API hydrates safe summaries from database or read model.
4. Result cards show denormalized public fields.

Scale notes:
- Do not compute expensive availability/rating joins for every search row at request time.
- Keep search index fresh through queue jobs.
- Support Arabic synonyms and typo tolerance.

## Notification Flow

1. Domain event occurs:
   - appointment created
   - payment reviewed
   - medication reminder due
   - lab result available
2. Notification job is queued.
3. User preferences are checked.
4. Notification is stored in database.
5. Push/SMS/email is dispatched if enabled.
6. Delivery state is logged.

Scale notes:
- Split urgent notifications from marketing.
- Add retry and dead-letter queues.
- Respect opt-out and medical privacy.

## File Upload / Download Flow

Upload:
1. API validates file size, type, and ownership.
2. File is stored on correct disk.
3. Visibility is recorded:
   - public for approved provider avatars/assets only
   - private for medical files, payment proofs, provider documents
4. Metadata is stored.
5. Virus scanning should be added before production.

Download:
1. Client requests file through backend route.
2. Backend checks authorization.
3. Backend returns signed temporary URL or streamed file.
4. Private storage path is never exposed in API responses.

## Monitoring And Alerting Plan

Minimum production monitoring:
- HTTP error rate.
- API latency p95/p99.
- Queue lag by queue.
- Database CPU, memory, slow queries, locks.
- Redis memory and eviction.
- Payment webhook failures.
- File upload failures.
- Login failure spikes.
- Admin review backlog.
- Notification delivery failures.
- Disk/storage growth.

Alert examples:
- Payment webhook verification failures > threshold.
- Queue lag > 5 minutes for payment/notification queues.
- 5xx rate > 1%.
- DB connections near max.
- Backup failure.

## Backup And Restore Plan

Required:
- Automated daily database backups.
- Point-in-time recovery where possible.
- Object storage backup/versioning.
- Backup encryption.
- Monthly restore drill.
- Documented RPO/RTO.
- Separate backup credentials.

Do not claim production readiness until restore drills are actually completed.

## Security And Privacy Plan

Required:
- HTTPS everywhere.
- `APP_DEBUG=false`.
- Strict CORS.
- Sanctum/token hardening.
- Role and ownership checks on every private resource.
- Private storage for medical files/payment proofs/provider documents.
- Audit logs for admin actions.
- Payment webhook HMAC validation.
- Rate limiting.
- Security headers.
- Secrets in environment manager, never repo.
- Data retention policy.
- Medical disclaimer and consent copy reviewed legally.

## Rate Limiting Plan

Suggested domains:
- Auth endpoints: strict per IP/device/email.
- Search/listing: moderate, cacheable.
- Booking/payment upload: stricter and idempotent.
- AI assistant: quota and safety throttles.
- Admin: authenticated and audited.

## Caching Plan

Cache candidates:
- specialties.
- cities/areas.
- public provider summaries.
- service categories.
- search filters.
- landing page static data.

Avoid caching:
- private medical files.
- payment proof URLs.
- rapidly changing payment status without short TTL/invalidation.
- appointment slot availability unless invalidation is strong.

## Queue Plan

Queues:
- `payments`
- `notifications`
- `files`
- `search-index`
- `ai`
- `reports`
- `default`

Rules:
- Payment queues get high reliability and alerting.
- AI/report jobs should not block payments.
- Dead-letter failed jobs.
- Track retry counts and failure reasons.

## Database Indexes Plan

Priority indexes:
- providers: type, status, active.
- provider_branches: provider, city, area, active, latitude/longitude.
- appointments: patient, doctor, provider, slot, status, scheduled_at.
- slots: schedule/date/start/status.
- payments: payable/paymentable, status, patient, created_at.
- pharmacy/lab orders: patient, provider, status, created_at.
- notifications: user, read_at, created_at.
- health records: patient/family member, measured_at.
- audit logs: actor, subject, event, created_at.

At scale:
- Measure query plans before adding indexes blindly.
- Add composite indexes for common filters.
- Archive old high-volume logs.

## Search Indexes Plan

Indexes:
- `providers`
- `doctor_services`
- `pharmacy_products`
- `lab_tests`
- `radiology_scans`
- `gyms`
- `coaches`

Common fields:
- Arabic name.
- English name.
- category/specialty.
- city/area.
- branch geo point.
- price/fee summary.
- rating summary.
- availability summary.
- home service flag.
- active/approved status.

Arabic support:
- Synonyms.
- Normalization of Arabic letter variants.
- Typo tolerance.
- Transliteration/English aliases for common terms.

## Honest Readiness

Current state:
- Suitable for architecture planning and supervised internal QA.
- Not approved for first pilot users until the physical payment/admin gate passes.
- Not production ready.
- Not proven for Egypt-scale or 10M users.

