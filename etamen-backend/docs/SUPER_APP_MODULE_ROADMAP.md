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
