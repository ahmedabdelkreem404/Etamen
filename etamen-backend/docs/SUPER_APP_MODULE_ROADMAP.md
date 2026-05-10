# Super App Module Roadmap

Date: 2026-05-08  
Sprint: 35 - Egypt-Scale Health Super App Architecture + Product Expansion Blueprint

This roadmap keeps the current MVP safe while preparing Etamen to become a broader Egyptian Health Super App.

## Phase A: Close Pilot Blockers

Goal:
- Prove the current doctor booking and manual payment workflow on a real Android device.

Backend tasks:
- Keep current doctor/payment APIs stable.
- Verify manual proof upload storage privacy.
- Verify admin accept path updates payment and appointment state.
- Confirm no private file paths in API responses.

Flutter tasks:
- Verify physical Android login.
- Verify booking.
- Verify proof upload from gallery/camera.
- Verify refreshed payment/appointment state after admin review.
- Verify logout/session restore.

Admin tasks:
- Document payment review SOP.
- Verify admin can view proof securely.
- Verify accept path.
- Test reject path if time allows.

Infrastructure tasks:
- Use a backend URL reachable from phone.
- Use seeded/staging-safe data.
- Do not rely on `127.0.0.1` from a physical device.

Testing tasks:
- Physical-device walkthrough.
- Backend tests.
- Flutter tests/build.
- Screenshot evidence.

Risks:
- Upload permission/device picker issues.
- Admin review state mismatch.
- Session restore issues on physical Android.

Exit criteria:
- No unresolved blocker in friction log.
- First pilot scope is explicit.
- Product owner reviews physical-device evidence.

## Phase B: Production MVP

Goal:
- Prepare a safe city-level production MVP after physical gate passes.

Backend tasks:
- Production environment configuration.
- Live/sandbox Paymob validation path.
- Queue workers and scheduler.
- Redis cache/queue.
- File privacy hardening.
- API rate limiting.
- Backup/restore procedure.
- Provider onboarding workflow.

Flutter tasks:
- Release signing.
- Production API base URL.
- Crash reporting.
- Push notification production token flow.
- Arabic/English QA.
- Device matrix testing.
- Upload proof testing on multiple phones.

Admin tasks:
- Provider approval SOP.
- Payment review SOP.
- Support/refund SOP.
- Legal/support content review.
- Role permissions review.

Infrastructure tasks:
- HTTPS.
- Domain.
- Managed database.
- Object storage.
- Monitoring and alerts.
- Error tracking.

Testing tasks:
- End-to-end staging test.
- Payment webhook test.
- Restore drill.
- Security smoke test.

Risks:
- Payment compliance.
- File privacy.
- Operational backlog.
- Production secrets management.

Exit criteria:
- Production checklist passes.
- Legal and support docs approved.
- Monitoring and backups verified.
- Pilot results justify wider rollout.

## Phase C: Expand Services

Goal:
- Add new verticals carefully after MVP reliability is proven.

Backend tasks:
- Radiology taxonomy and order lifecycle.
- Coach/gym provider categories.
- Family members.
- Health document vault.
- Insurance filters later.
- Provider app/admin improvements.

Flutter tasks:
- Radiology discovery and order UI.
- Gym/coach discovery UI.
- Family profile UI.
- Health document vault UI.
- Clear coming-soon/hidden states until backend is ready.

Admin tasks:
- Radiology center approval.
- Gym/coach approval.
- Document moderation.
- Result upload review.
- Service catalog management.

Infrastructure tasks:
- Object storage capacity planning.
- Search engine planning.
- Queue separation for files/results.

Testing tasks:
- Module-specific API tests.
- Flutter flow tests.
- Privacy tests for documents/results.

Risks:
- Medical report privacy.
- Half-built modules confusing users.
- Provider operational complexity.
- Regulatory/legal review for sensitive areas.

Exit criteria:
- Each vertical has complete lifecycle before public exposure.
- Admin can operate the vertical.
- Patient UI has no broken dead ends.

## Phase D: Scale Architecture

Goal:
- Make Etamen technically ready for Egypt-wide growth.

Backend tasks:
- Redis.
- Queues by domain.
- Search engine integration.
- Object storage/CDN split.
- Read replicas.
- Query profiling.
- Audit/event pipelines.

Flutter tasks:
- Pagination and infinite-list robustness.
- Offline/poor-network UX.
- Performance profiling.
- Upload retry/resume behavior where appropriate.

Admin tasks:
- Operations dashboards.
- Payment backlog dashboards.
- Provider approval dashboards.
- Fraud/moderation queues.

Infrastructure tasks:
- Load balancer.
- Horizontal app nodes.
- Autoscaled queue workers.
- Search cluster.
- CDN.
- WAF/API gateway.

Testing tasks:
- k6/JMeter load tests.
- Search benchmark.
- Queue throughput test.
- Upload/download stress test.
- Payment webhook stress test.

Risks:
- Premature distributed complexity.
- Search index staleness.
- Queue lag in critical workflows.
- Cost growth.

Exit criteria:
- Load tests meet target SLOs.
- Alerts cover critical paths.
- Incident response is rehearsed.

## Phase E: Egypt-Wide Marketplace

Goal:
- Build the business and operations layer for national expansion.

Backend tasks:
- Provider acquisition tools.
- Public website CMS/SEO.
- City/area expansion tools.
- Review moderation.
- Fraud/risk workflows.
- Commission and settlement automation.

Flutter tasks:
- Marketplace discovery refinements.
- Insurance filters.
- Family subscriptions/loyalty if approved.
- City/area personalization.

Admin tasks:
- Operations dashboard.
- Provider CRM.
- Moderation queue.
- Dispute management.
- Settlement dashboards.
- Support analytics.

Infrastructure tasks:
- Data warehouse.
- Analytics pipeline.
- Multi-region disaster recovery plan if justified.
- Advanced WAF/DDoS controls.

Testing tasks:
- National-scale load testing.
- SEO checks.
- Fraud workflow drills.
- Disaster recovery drill.

Risks:
- Operational quality variance by city.
- Content/review moderation volume.
- Payment disputes/refunds.
- Regulatory complexity.

Exit criteria:
- Marketplace operations can support city expansion.
- Search and support performance remain stable.
- Finance/reconciliation is reliable.

## Sprint 36 Implementation Update

Implemented as foundation, not full vertical launch:
- Expanded provider types to include hospitals, clinics, medical centers, radiology, gyms, fitness coaches, nutrition coaches, physiotherapy, and home healthcare.
- Added minimal type-specific profile tables for future provider verticals.
- Hardened branch/location fields for address, map coordinates, working hours, and service radius.
- Added explicit provider document visibility rules.
- Added `needs_changes` onboarding state and admin request-changes action.
- Added hospital departments and hospital-doctor link foundation.
- Added generic `service_categories` and `provider_services`.
- Added `provider_booking_settings`.
- Added `provider_contracts`.
- Added admin/Filament foundation resources for the new operational tables.
- Added tests for public API safety and existing MVP preservation.

Still deferred:
- Patient-facing radiology/gym/coach/home healthcare screens.
- Full order/booking lifecycle for new verticals.
- Public hospital pages.
- Production launch hardening.
- Egypt-scale load proof.

## Sprint 37 Implementation Update

Implemented as backend/admin radiology catalog foundation, not patient launch:
- Added `radiology_scan_categories` with seeded Arabic-first scan taxonomy.
- Added `radiology_scans` for provider/admin-managed scan catalog.
- Added `radiology_preparation_instructions` with required general-instructions disclaimer.
- Added protected provider APIs for owning radiology providers to manage their own scans.
- Added protected admin APIs and Filament resources for scan categories, scans, and preparation instructions.
- Added safe read-only catalog endpoints filtered to approved active radiology providers and active scans.
- Added local/staging demo radiology data to `PilotDemoSeeder`.
- Added feature tests for permissions, branch ownership, public visibility, disclaimers, and private path safety.

Still deferred:
- Flutter radiology screens.
- Patient radiology orders, payments, refunds, and result delivery.
- DICOM/image/report storage strategy.
- Medical/legal review of preparation content.
- Public launch readiness for radiology.

## Sprint 42 Implementation Update

Implemented locally:

- Added patient-safe public hospital APIs for list, details, departments, hospital doctors, and department doctors.
- Added one approved demo hospital to `PilotDemoSeeder`.
- Added five active departments and linked approved active demo doctors with schedules and slots.
- Added Flutter Hospitals section under Services.
- Added hospital details, location summary, capability badges, departments, and department doctors screens.
- Reused the existing doctor profile, booking, and payment flow from hospital discovery.
- Added backend and Flutter tests for the hospital section.

Still deferred:

- Staging deployment of hospital APIs/screens.
- Hospital-specific appointment context persisted in booking records.
- Hospital admin analytics/reporting.
- Real hospital onboarding and document review.
- Public launch readiness.

## Sprint 44 Implementation Update

Implemented locally as backend foundation only:

- Added radiology order tables and status lifecycle.
- Added patient order APIs.
- Added provider order management and result upload APIs.
- Added admin order/result APIs and Filament resources.
- Integrated radiology orders with existing manual payment proof/admin accept flow.
- Added secure private result metadata and download authorization.
- Added feature tests for pricing, payment, provider scoping, result privacy, and admin filters.

Still deferred:

- Flutter radiology screens.
- Staging deployment of radiology order APIs.
- Public radiology launch.
- DICOM/image viewer.
- Refund/settlement policy for radiology orders.

## Sprint 45 Implementation Update

Implemented locally in Flutter:

- Patient-facing Radiology entry under Services.
- Radiology catalog with Arabic-first categories and scan cards.
- Radiology order builder.
- Radiology order details with payment and result sections.
- Reuse of existing manual payment method and proof upload flow.
- Flutter status refresh after admin payment acceptance.
- Safe result metadata and local download action.

Accepted locally:

- order creation.
- manual proof upload from emulator.
- admin accept.
- paid/result-ready status reflection in Flutter.
- no raw private path shown to patient.

Still deferred:

- staging deployment.
- real phone radiology proof upload.
- production result download hardening across device matrix.
- provider-facing mobile radiology UI.
- public launch readiness.

## Sprint 46 Implementation Update

Implemented locally as backend foundation only:

- Gym discovery APIs.
- Gym membership plans and classes.
- Gym booking/payment lifecycle foundation.
- Coach discovery APIs.
- Coach session types, availability slots, and packages.
- Coach booking/payment lifecycle foundation.
- Provider-owned management APIs.
- Admin listing APIs.
- Filament resources for gym/coach catalog, bookings, and histories.
- Demo seed data for one gym, one fitness coach, and one nutrition coach.
- Feature tests for public visibility, provider scoping, payment proof/admin accept, and privacy.

Still deferred:

- Flutter gym/coach screens.
- local emulator UI QA for gym/coach.
- staging deployment.
- production operations.
- advanced subscriptions, class capacity enforcement, refunds, attendance, and coach progress plans.

## Sprint 47 Implementation Update

Implemented locally in Flutter:

- Services entries for Gyms and Coaches.
- Gym list/details/my bookings/booking details UI.
- Coach list/details/my bookings/booking details UI.
- Flutter fitness models, repositories, controllers, widgets, and route wiring.
- Payment route context for `gymBookingId` and `coachBookingId`.
- Fitness JSON parsing fix so booking responses keep backend-owned `status`, `payment_id`, and `booking_number`.

Partially verified locally:

- local backend health.
- public gym and coach discovery.
- active manual payment methods.
- local Android emulator APK install/launch.
- coach list/details rendering.

Still deferred:

- complete gym payment proof/admin accept from Flutter.
- complete coach payment proof/admin accept from Flutter.
- staging deployment.
- real phone fitness proof upload.
- production marketplace readiness.

Decision:

```text
LOCAL_FITNESS_PAYMENT_UI_BLOCKED
```

## Sprint 50 Implementation Update

Implemented locally as workspace/provider foundation:

- Backend-owned `/api/v1/me/workspaces` contract.
- Provider staff permission layer on top of existing `owner`, `admin`, and `staff` roles.
- Provider dashboard summary endpoint for active same-provider staff only.
- Provider staff management foundation for existing users by email.
- Flutter account workspace section.
- Flutter workspace switcher.
- Flutter provider dashboard shell.
- Flutter platform admin shell.

Accepted locally:

- patient workspace.
- doctor provider dashboard.
- hospital provider dashboard.
- radiology provider dashboard.
- gym provider dashboard.
- coach provider dashboard.
- limited staff dashboard with filtered permissions.

Still deferred:

- full provider operational pages.
- provider invitation emails.
- staging deployment.
- real-phone workspace QA.
- public launch readiness.

Decision:

```text
LOCAL_WORKSPACE_PROVIDER_DASHBOARD_ACCEPTED
```
## Sprint 51 Local Provider Operations MVP

Sprint 51 adds the first limited provider operations layer inside the unified workspace model.

Accepted local scope when tests/build/QA pass:

- workspace-scoped provider operation APIs
- doctor appointment list/details/status actions
- hospital context appointment list plus departments/doctors read-only pages
- radiology order list/details/status actions
- pharmacy orders/products read-only provider pages
- lab orders/catalog read-only provider pages
- gym bookings/plans/classes provider pages
- coach bookings/availability/session types/packages provider pages
- backend permission guards for every provider operation

Still not approved:

- full provider portal replacement
- staging deployment
- real phone provider dashboard QA
- public launch
- production readiness

Decision after Sprint 52 QA completion:

```text
LOCAL_PROVIDER_OPERATIONS_MVP_ACCEPTED
```

## Sprint 52 Provider Operations QA Completion

Sprint 52 did not add a new vertical. It completed the missing emulator evidence and polish for Sprint 51.

Accepted locally:

- doctor provider operation pages
- hospital provider operation pages
- radiology provider operation pages
- pharmacy/lab read-only provider MVP pages
- gym provider operation pages
- coach provider operation pages, including packages quick action
- limited staff permission blocking
- provider operation privacy sweep

Still deferred:

- full provider portal
- provider result upload UI
- pharmacy/lab deeper provider write actions
- staging deployment
- real-phone provider operations QA
- public launch readiness

## Sprint 53 Real Phone Local Gate

Sprint 53 tested the local super-app APK on an Infinix X657C running Android 10 against a LAN backend URL.

Accepted locally on a real phone:

- auth/session/logout
- doctor booking, real proof upload, and admin accept
- radiology order, real proof upload, admin accept, visible result metadata, and download success state
- gym booking, real proof upload, and admin accept
- coach booking, real proof upload, and admin accept
- provider workspace dashboards for doctor, hospital, radiology, gym, and coach
- limited staff restricted workspace behavior
- local privacy/security sweep

Still not approved:

- staging readiness
- Hostinger readiness
- public launch
- production readiness
- app store readiness

## Sprint 54 Staging Gate Update

Sprint 54 attempted to move the accepted local real-phone behavior to staging, but deployment could not proceed because server access remains blocked.

Staging baseline:

- Landing and health respond.
- Readiness still fails: JSON request returns 401 and default/browser-style request returns 500 with `Route [login] not defined.`
- Payment methods are empty.
- Hospitals, gyms, and coaches endpoints are missing/stale on staging.
- Radiology scans endpoint responds but has empty data.
- Demo login accounts are not available on staging.

Decision:

```text
STAGING_ACCESS_BLOCKED
```

Still required before any supervised staging pilot consideration:

- restore SSH/Hostinger deployment access.
- back up staging database.
- deploy latest `main`.
- run safe migrations and staging demo seed.
- fix readiness from server logs.
- rebuild staging APK.
- pass real Android phone doctor proof upload/admin accept.
- pass staging provider workspace and limited staff guard.

This does not approve production, public launch, app-store release, or external user invitations.

## Sprint 55 Access-First Staging Recovery

Sprint 55 retried the staging access gate and remains blocked before deployment.

Result:

```text
STAGING_ACCESS_BLOCKED
```

Current staging diagnostics:

- health responds with HTTP 200.
- readiness still returns 401 with JSON accept and 500 by default with `Route [login] not defined.`
- payment methods are still empty.
- hospitals, gyms, and coaches routes still return 404.
- radiology scans are still empty.
- demo patient login is not available on staging.

No backup, migration, seed, or deployment was run because SSH/Hostinger access is still unavailable.

Next roadmap gate:

- restore safe server access.
- back up staging DB and `.env`.
- deploy latest `main`.
- run safe migrations and staging seed.
- fix readiness.
- then rerun staging phone proof/provider workspace QA.
