# Super App Architecture Audit

Date: 2026-05-08

Sprint 35 audit scope: document where Etamen really stands before expanding into an Egypt-scale health super app. This is not a production readiness claim and not a load-test result.

## Current Backend Shape

Etamen backend is a Laravel modular monolith with versioned JSON APIs under `/api/v1`, Filament admin resources, Sanctum authentication, role-based access, and private file download controllers.

Current backend modules under `app/Modules`:

| Module | Current responsibility |
| --- | --- |
| Identity | Register, login, logout, current user, roles. |
| Patients | Patient profile. |
| Providers | Provider registration/approval, doctors/pharmacies/labs, branches, documents, public discovery. |
| Locations | Cities and areas. |
| Appointments | Doctor schedules, slots, booking, status lifecycle, reviews. |
| Payments | Payment methods, Paymob foundation, manual proof upload/review, invoices, refunds table. |
| Wallets | Provider wallets, commissions, withdrawals, settlements, subscriptions foundation. |
| Pharmacies | Products, prescriptions, pharmacy orders, provider/admin lifecycle. |
| Labs | Lab tests, packages, lab orders, result upload/download. |
| Health | Health profile, chronic diseases, allergies, current medications, surgeries, goals, vitals. |
| Medications | Medication reminders, reminder times, logs, refills, notification queue. |
| CarePlans | Care plans, days, meals, food items, instructions, check-ins, meal logs, progress. |
| AI | Safety-gated assistant, context preview, provider configs, usage logs, safety events. |
| Notifications | In-app notifications, tokens, preferences, dispatches, templates, scheduler runs. |
| MedicalFiles | Private uploaded file metadata. |
| AuditLogs | Security/audit event log. |
| Settings | Runtime settings. |
| System | Health/readiness endpoints. |

## Current Provider Types

`ProviderType` currently supports:

- `doctor`
- `pharmacy`
- `lab`

Missing for super-app vision:

- `radiology`
- `gym`
- `fitness_coach`
- `nutrition_coach`
- `physiotherapy`
- `home_healthcare`
- `mental_health_center` or specialty-specific provider modeling, if approved later

## Current User Roles

`UserRole` currently supports:

- `super_admin`
- `admin`
- `patient`
- `doctor`
- `pharmacy_admin`
- `lab_admin`

Provider staff roles:

- `owner`
- `admin`
- `staff`

Missing future roles likely needed:

- radiology admin/staff
- gym admin/staff/trainer
- coach
- support agent
- finance operator
- medical content moderator
- operations manager
- read-only auditor

## Current Tables

Core Laravel / auth:

- `users`, `personal_access_tokens`, `password_reset_tokens`, `sessions`
- `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`
- Spatie permission tables from `2026_05_04_174904_create_permission_tables.php`

Domain tables:

| Domain | Existing tables |
| --- | --- |
| Providers | `providers`, `provider_branches`, `provider_staff`, `provider_documents`, `provider_approval_requests`, `specialties`, `doctor_profiles`, `doctor_specialties`, `pharmacy_profiles`, `lab_profiles` |
| Locations | `cities`, `areas` |
| Files | `uploaded_files` |
| Appointments | `doctor_schedules`, `doctor_schedule_days`, `doctor_holidays`, `appointment_slots`, `appointments`, `appointment_notes`, `appointment_status_histories`, `appointment_reviews` |
| Payments | `payment_methods`, `payments`, `payment_attempts`, `payment_proofs`, `payment_status_histories`, `invoices`, `refunds` |
| Wallets | `wallets`, `wallet_transactions`, `commission_rules`, `withdrawal_requests`, `settlements`, `settlement_items`, `subscription_plans`, `provider_subscriptions` |
| Pharmacy | `pharmacy_products`, `pharmacy_prescriptions`, `pharmacy_orders`, `pharmacy_order_items`, `pharmacy_order_status_histories` |
| Labs | `lab_tests`, `lab_packages`, `lab_package_items`, `lab_orders`, `lab_order_items`, `lab_order_status_histories`, `lab_results` |
| Health | `health_profiles`, `patient_chronic_diseases`, `patient_allergies`, `patient_current_medications`, `patient_surgeries`, `health_goals`, `vital_records`, `health_access_logs` |
| Medications | `medication_reminders`, `medication_reminder_times`, `medication_logs`, `medication_refill_events`, `medication_notification_queue` |
| Care plans | `care_plans`, `care_plan_days`, `care_plan_meals`, `care_plan_food_items`, `care_plan_instructions`, `care_plan_checkins`, `meal_logs` |
| AI | `ai_conversations`, `ai_messages`, `ai_provider_configs`, `ai_usage_logs`, `ai_safety_events` |
| Notifications | `notifications`, `notification_tokens`, `notification_preferences`, `notification_templates`, `notification_dispatches`, `scheduler_runs` |
| Ops | `settings`, `audit_logs` |

## Current APIs

The backend exposes about 287 routes under `/api/v1`. Main API surfaces:

- Public discovery: doctors, doctor details, slots, pharmacies, labs, specialties, cities, areas.
- Authenticated patient: appointments, payments, pharmacy orders, lab orders, health, medications, care plans, AI, notifications, account.
- Provider: profile/branches/documents, doctor schedules/appointments, pharmacy products/orders, lab tests/packages/orders/results, wallet/withdrawals, care plans.
- Admin: provider approvals, payment review, appointments, pharmacy/lab orders, health read routes, AI monitoring, notifications, wallets/settlements/withdrawals, Filament resources.

## Current Flutter Shape

Flutter patient app features under `lib/features`:

- account, auth, home, doctors, appointments, payments
- pharmacy, labs
- health, medications, care plans
- AI assistant, notifications, splash

Current Flutter does not contain radiology, gym, coach, family account, insurance, emergency, or medical document vault screens as full product modules.

## Current Admin Capabilities

Filament resources already cover:

- Users, providers, provider branches/documents/approval requests, specialties.
- Doctor profiles, schedules, slots, holidays, appointments, appointment reviews/history.
- Payments, payment proofs, attempts, invoices, payment histories, methods.
- Wallets, wallet transactions, commission rules, withdrawals, settlements.
- Pharmacies/products/orders/prescriptions.
- Labs/tests/packages/orders/results.
- Health profiles/vitals/access logs.
- Medications/reminders/logs/refills/notification queue.
- Care plans/check-ins/meals/foods/instructions.
- AI conversations/messages/provider configs/usage/safety events.
- Notifications/tokens/preferences/templates/dispatches/scheduler runs.
- Settings, audit logs, uploaded files.

Missing future admin domains:

- Radiology catalog/orders/results.
- Gym branches/plans/classes/trainers.
- Coach onboarding/sessions/plans/progress.
- Support ticketing and dispute workflow.
- Moderation/fraud dashboards.
- Search index monitoring.
- Public website CMS/content moderation.

## Payment / Wallet Capability

What is already good:

- Frontend does not verify payments.
- Manual payment proof upload requires admin review.
- Paymob callback/webhook has HMAC verification.
- Payments are tied to payable entities.
- Wallet balance is ledger-derived from `wallet_transactions`.
- Commission, holds, releases, withdrawals, settlements have tests.
- Idempotency exists for key money flows.

What is missing:

- Physical-device proof upload/admin review gate is still unverified.
- Production Paymob live configuration and webhook reachability are not proven.
- Refund automation is table-level/foundation only.
- Provider bank transfer integration is not implemented.
- Chargeback/dispute/fraud operations are not productized.

## File Storage / Privacy Model

Current model:

- `uploaded_files` stores owner, uploader, disk, path, original name, MIME type, size, category, visibility, checksum, metadata.
- Provider documents and lab results are private and downloaded through authorized controllers.
- Doctor `avatar_url` is safe public only and explicitly blocks private/provider-document paths.

Good:

- Private medical/provider files are not exposed as raw storage paths.
- Tests cover unauthorized file access and hidden internal fields.

Missing before production:

- Object storage/private bucket setup.
- Signed URL policy or streamed download strategy at scale.
- Malware scanning / file type hardening.
- Retention/deletion policy and audit trail for sensitive medical documents.

## Current Bottlenecks

- Single Laravel app and single DB assumption.
- Public discovery currently DB-backed; no dedicated search engine.
- Location search is city/area/basic lat-long, not full geo-radius marketplace search.
- Some future listing needs like next available slot can become N+1 or expensive if added naively.
- Queue/scheduler foundations exist, but production queue workers, retry dashboards, dead-letter handling, and observability are not proven.
- Filament admin can become slow with huge tables unless resources add filters, indexes, scoped pagination, and async exports.
- No load testing has proven 10M user behavior.

## Missing Modules

- Radiology centers and scan catalog/orders/results.
- Gyms, gym branches, memberships, classes, trainers.
- Fitness and nutrition coaches, sessions, plans, progress logs.
- Family accounts / dependent profiles.
- Health document vault beyond generic uploads tied to flows.
- Insurance acceptance/claims.
- Support tickets/disputes.
- Public website CMS/SEO/provider acquisition.
- Fraud/moderation/risk queues.
- Full search/location index.

## Current Scale Risks

| Risk | Why it matters |
| --- | --- |
| Search on transactional DB | Provider and catalog discovery will be high-read/high-filter traffic. |
| Location radius queries | Egypt-wide radius search needs geo indexes or search engine support. |
| Appointment concurrency | Slot booking must remain transactional and idempotent under bursts. |
| Payment webhooks | Must survive retries, duplicates, gateway latency, and admin mistakes. |
| File volume | Lab/radiology documents can dominate storage and bandwidth. |
| Notifications | Reminder/payment/order traffic needs queues and worker capacity. |
| Admin operations | Large review queues need filters, roles, auditability, and SOPs. |
| Privacy | Medical files, AI context, and provider documents need stricter production controls. |

## What Is Already Good

- Modular monolith is a good stage-1/2 architecture.
- Tests cover critical security, payment, wallet, provider, appointment, pharmacy, lab, health, AI, and notification behavior.
- Public/private API separation is explicit.
- Backend owns price/status/payment verification.
- Arabic-first patient app exists.
- Manual ops/admin foundation is substantial.

## Must Change Before Production

- Complete physical Android proof upload/admin payment review gate.
- Deploy HTTPS production/staging with correct API base URLs.
- Configure queue workers, scheduler, object storage, backups, monitoring, error tracking.
- Run security review for CORS, headers, rate limits, admin roles, file access.
- Configure live Paymob and webhook URLs.
- Add FCM/notification provider production setup if push is enabled.
- Finalize legal/support/refund/provider approval SOPs.
- Add load smoke tests for key APIs before public launch.

## Must Change Before Egypt-Scale

- Introduce Redis cache/queues and tune workers.
- Add search engine for provider/catalog discovery.
- Add read replicas and query/index monitoring.
- Move uploaded files to object storage with CDN for public assets and private controlled downloads.
- Split heavy domains into separate queues.
- Add WAF/API gateway/rate-limits by route class.
- Add data warehouse/analytics and audit retention strategy.
- Add incident response, SLOs, capacity planning, load testing, and disaster recovery drills.

## Honest Readiness

- Supervised pilot: not approved until physical-device payment proof/admin review/logout gate passes.
- Production launch: not ready.
- 10M users / 1M providers: not proven and not ready. The current codebase is a strong MVP modular monolith, not an Egypt-scale proven platform.

