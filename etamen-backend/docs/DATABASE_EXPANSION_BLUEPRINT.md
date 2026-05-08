# Database Expansion Blueprint

Date: 2026-05-08

This is a design blueprint. Sprint 35 intentionally does not add all migrations because half-built modules would create dead product surfaces and operational risk.

Privacy levels:

- Public: safe catalog/discovery data.
- User-private: patient-owned health/order data.
- Provider-private: provider documents/ops data.
- Admin-sensitive: payment review, audit, fraud, moderation.
- Medical-private: clinical documents/results/context.

## Core Taxonomy

| Table | Purpose | Critical fields | Indexes | Privacy | Exists now / later | Scale notes |
| --- | --- | --- | --- | --- | --- | --- |
| `medical_specialties` / current `specialties` | Doctor specialty taxonomy | name_ar, name_en, slug, active | slug unique, active | Public | Exists as `specialties`; may rename later only with migration plan | Keep stable slugs; add synonyms later. |
| `medical_subspecialties` | Fine-grained specialties | specialty_id, names, slug, active | specialty_id, slug | Public | Later | Needed for large doctor catalog. |
| `provider_categories` | Broader provider verticals | code, names, active | code unique | Public | Later | Use before adding radiology/gym/coach provider types. |
| `service_categories` | Catalog grouping across services | provider_category_id, names, slug | provider_category_id, slug | Public | Later | Powers marketplace discovery. |
| `cities` | Cities | names, active | active/name | Public | Exists | Add governorate if needed. |
| `areas` | Areas inside cities | city_id, names, active | city_id, active | Public | Exists | Add normalized Arabic names. |
| `districts` | More granular areas | area_id/city_id, names | city_id, area_id | Public | Later | Useful for Cairo/Giza/Alex scale. |
| `geo_zones` | Delivery/service polygons | provider/category, polygon/center, radius | provider_id, category, geospatial | Public/admin | Later | Avoid naive distance logic for delivery coverage. |

## Doctors

| Table | Purpose | Critical fields | Indexes | Privacy | Exists now / later | Scale notes |
| --- | --- | --- | --- | --- | --- | --- |
| `doctor_profiles` | Doctor business profile | provider_id, user_id, title, bio, fee, years, avatar_path | provider_id unique, user_id unique | Public/safe | Exists | Add approved media flow before real photos. |
| `doctor_specialties` | Specialty pivot | doctor_profile_id, specialty_id | unique pair | Public | Exists | Good. |
| `doctor_subspecialties` | Subspecialty pivot | doctor_profile_id, subspecialty_id | unique pair | Public | Later | Use for precise discovery. |
| `doctor_services` | Doctor-offered services | doctor_profile_id, service name, duration, price source | doctor_profile_id | Public/admin | Later | Backend must own prices. |
| `doctor_media` | Approved images/videos | doctor_id, file_id/public_path, type, status | doctor_id, status | Public/admin | Later | No private docs; approval required. |
| `doctor_reviews_summary` | Cached review aggregate | doctor_id, average, count, updated_at | doctor_id unique | Public | Later if needed | Current can compute from visible reviews; cache when scale demands. |
| `doctor_insurances` | Accepted insurance | doctor_id, insurance_provider_id | doctor_id/provider | Public | Later | Needs insurance policy first. |

## Pharmacies

| Table | Purpose | Critical fields | Indexes | Privacy | Exists now / later | Scale notes |
| --- | --- | --- | --- | --- | --- | --- |
| `pharmacy_profiles` | Pharmacy settings | provider_id, license, delivery_available | provider_id unique | Public/provider | Exists | Add delivery SOP. |
| `pharmacy_branches` / `provider_branches` | Branches | provider_id, city/area, lat/lng, active | provider_id, city/area, geo | Public | Exists as generic provider branches | Generic branch table is good. |
| `pharmacy_products` | Product catalog | provider_id, names, price, stock, prescription_required, active | provider_id, active, name | Public | Exists | Product search needs index later. |
| `pharmacy_inventory` | Per-branch inventory | branch_id, product_id, quantity/reserved | branch/product unique | Provider/admin | Later | Needed when branch stock matters. |
| `pharmacy_delivery_zones` | Delivery coverage/fees | pharmacy/branch, geo zone, fee, min order | geo/provider | Public/admin | Later | Required for nearby order accuracy. |
| `prescription_reviews` | Pharmacist review workflow | prescription_id/order_id, reviewer_id, status, notes | status, pharmacy_id | Medical-private/admin | Later | Do not expose medical notes broadly. |

## Labs

| Table | Purpose | Critical fields | Indexes | Privacy | Exists now / later | Scale notes |
| --- | --- | --- | --- | --- | --- | --- |
| `lab_profiles` | Lab settings | provider_id, license, home_collection_available | provider_id unique | Public/provider | Exists | Good. |
| `lab_branches` / `provider_branches` | Lab locations | provider_id, city/area, lat/lng | provider_id, geo | Public | Exists as generic provider branches | Good. |
| `lab_test_catalog` / current `lab_tests` | Tests | provider_id, names, price, sample_type, active | provider_id, active, name | Public | Exists as `lab_tests` | Consider global catalog + provider pricing later. |
| `lab_test_categories` | Test categories | names, slug | slug | Public | Later | Improves UX/search. |
| `lab_packages` | Bundled tests | provider_id, names, price, active | provider_id, active | Public | Exists | Good. |
| `lab_result_files` / current `lab_results` | Result metadata/file | order_id, file_id, uploaded_by | order_id | Medical-private | Exists as `lab_results` | Add abnormal disclaimer metadata, not diagnosis. |

## Radiology

| Table | Purpose | Critical fields | Indexes | Privacy | Exists now / later | Scale notes |
| --- | --- | --- | --- | --- | --- | --- |
| `radiology_profiles` | Radiology provider profile | provider_id, license, home_service/report_delivery/dicom flags | provider_id unique | Public/provider | Exists Sprint 36 | Minimal profile foundation; not full product lifecycle. |
| Generic `provider_branches` | Locations | provider_id, city/area, lat/lng, working hours, service radius | provider_id, geo fields | Public-safe | Exists Sprint 36 | Reused by radiology; no separate radiology branch table needed now. |
| `radiology_scan_categories` | X-Ray, Ultrasound, CT, MRI, Mammography, Doppler, Echo, ECG, dental panorama, DEXA | code, names, active, sort_order | code unique, active/sort | Public | Exists Sprint 37 | Arabic-first stable taxonomy. |
| `radiology_scans` | Scans offered | provider_id, branch_id, category_id, names, price, duration, active, prep flags | provider/active, category/active, branch/active | Public-safe catalog/admin | Exists Sprint 37 | Backend/admin/provider price authority; no patient writes. |
| `radiology_orders` | Patient order/booking | patient_id, provider_id, branch_id, status, total | patient/status, provider/status | User-private/provider | Later | Similar to labs but scan-specific. |
| `radiology_order_items` | Ordered scans | order_id, scan_id, snapshot price/name | order_id | User-private | Later | Snapshot catalog values. |
| `radiology_result_files` | Reports/images | order_id, file_id, type, status | order_id/type | Medical-private | Later | Large storage; maybe external DICOM links. |
| `radiology_preparation_instructions` | Prep text | scan_id/category_id, ar/en text, warnings, active, sort_order | scan/active, category/active | Public-safe catalog/admin | Exists Sprint 37 | Carries general-instructions disclaimer; must be reviewed medically before broad use. |

## Gyms

| Table | Purpose | Critical fields | Indexes | Privacy | Exists now / later | Scale notes |
| --- | --- | --- | --- | --- | --- | --- |
| `gym_profiles` | Gym provider profile | provider_id, amenities, gender policy, active | provider_id unique | Public | Later | Requires provider type. |
| `gym_branches` / generic branches | Gym locations | provider_id, geo, hours | provider_id, geo | Public | Later | Reuse branch model. |
| `gym_membership_plans` | Plans/passes | provider_id, branch_id, name, duration, price, active | provider/branch/active | Public | Later | Price from backend only. |
| `gym_classes` | Classes | branch_id, trainer_id, schedule, capacity | branch/date | Public/provider | Later | Needs booking capacity logic. |
| `gym_trainers` | Trainer profiles | gym_id, user/profile fields | gym_id | Public/provider | Later | Might overlap coach profiles. |
| `gym_reviews` | Reviews | user_id, gym_id, rating, visible | gym_id, visible | Public/admin | Later | Moderation needed. |

## Coaches

| Table | Purpose | Critical fields | Indexes | Privacy | Exists now / later | Scale notes |
| --- | --- | --- | --- | --- | --- | --- |
| `coach_profiles` | Coach profile | user_id/provider_id, type, bio, credentials, fee | type/status | Public/provider | Later | Separate fitness/nutrition if needed. |
| `coach_specialties` | Weight loss, muscle gain, rehab fitness, diabetes nutrition, sports nutrition | coach_id, specialty_id | unique pair | Public | Later | Claims need moderation. |
| `coach_schedules` | Availability | coach_id, days/times | coach/date | Public/provider | Later | Similar to doctor schedules. |
| `coach_sessions` | Booked sessions | coach_id, patient_id, slot, status, payment_id | coach/date, patient/status | User-private/provider | Later | Can reuse appointment patterns. |
| `coach_plans` | Assigned plans | coach_id, patient_id, goals, status | patient/status | User-private | Later | Avoid medical treatment claims. |
| `coach_progress_logs` | Progress entries | plan_id, metrics, notes | plan/date | User-private | Later | Privacy/audit needed. |

## User Health

| Table | Purpose | Critical fields | Indexes | Privacy | Exists now / later | Scale notes |
| --- | --- | --- | --- | --- | --- | --- |
| `family_members` | Dependents | owner_user_id, relation, name, dob, consent/status | owner/status | User-private | Later | Critical for children/elderly workflows. |
| `health_profiles` | Health profile | patient_id, demographics, basics | patient unique | Medical-private | Exists | Good. |
| `chronic_diseases` / current `patient_chronic_diseases` | Patient diseases | patient_id, name, diagnosed_at | patient | Medical-private | Exists as patient table | Consider taxonomy later. |
| `allergies` / current `patient_allergies` | Allergies | patient_id, allergen, severity | patient | Medical-private | Exists | Good. |
| `medications` / current reminders/current meds | Current meds/reminders | patient_id, name, schedule | patient/status | Medical-private | Exists across health/meds | Keep non-prescriptive copy. |
| `vital_records` | Vitals | patient_id, type, value, measured_at | patient/type/date | Medical-private | Exists | Partition/archive later if huge. |
| `health_documents` | Vault documents | patient/family_member, file_id, category, date | owner/category/date | Medical-private | Later | Requires consent/sharing. |
| `care_plans` / logs | Care plans | patient_id, creator, status | patient/status | Medical-private | Exists | Good. |

## Search / Location

| Table | Purpose | Critical fields | Indexes | Privacy | Exists now / later | Scale notes |
| --- | --- | --- | --- | --- | --- | --- |
| `provider_locations` | Normalized geo points | provider_id, branch_id, lat/lng, geohash, active | geo/geohash, provider | Public | Later | Better than scanning branch table. |
| `provider_search_index` | Denormalized provider search | provider_id, category, names, specialty, area, rating, availability | category/area/rating/search engine | Public | Later | Prefer external search engine at scale. |
| `service_search_index` | Catalog item search | service_type, provider_id, names, price, active | type/provider/search | Public | Later | Labs/radiology/pharmacy/gym/coaches. |
| Geohash fields | Radius fallback | geohash prefix, lat/lng | geohash | Public | Later | Use if DB supports spatial/geohash. |

## Payments

| Table | Purpose | Critical fields | Indexes | Privacy | Exists now / later | Scale notes |
| --- | --- | --- | --- | --- | --- | --- |
| `payments` | Payable payment record | payable_type/id, user_id, provider, amount, status | payable, provider/status, user/status | User-private/admin | Exists | Good polymorphic foundation. |
| `payment_attempts` | Gateway attempts | payment_id, method_type, gateway_reference, status | gateway_reference, payment_id | Admin-sensitive | Exists | Good for Paymob retries. |
| `payment_proofs` | Manual proof | payment_id, file_id, status, reviewer | payment/status | Medical/payment private | Exists | Physical gate pending. |
| `invoices` | Invoice | payment_id, number, gross/net/commission | payment unique, number unique | User/provider/admin | Exists | Good. |
| `refunds` | Refund requests | payment_id, amount, reason, status | payment/status | Admin-sensitive | Exists foundation | Needs workflow/API later. |
| `wallet_transactions` | Ledger | wallet_id, type, amount, status/ref | wallet/date/type | Provider/admin | Exists | Good ledger. |
| `settlements` | Settlement batch | provider/admin, status, paid_at | status/provider | Admin-sensitive | Exists | Good. |
| `provider_commissions` / current `commission_rules` | Commission config | provider_type/service, percent/fixed, active | active/type | Admin-sensitive | Exists as rules | Expand per service later. |

## Operations

| Table | Purpose | Critical fields | Indexes | Privacy | Exists now / later | Scale notes |
| --- | --- | --- | --- | --- | --- | --- |
| `support_tickets` | Patient/provider support | user_id, category, status, priority | status/category/user | User-private/admin | Later | Needed before public launch. |
| `audit_logs` | Audit trail | actor, action, subject, metadata | actor, subject, action/date | Admin-sensitive | Exists | Retention policy needed. |
| `admin_actions` | Explicit admin decisions | admin_id, action, target, reason | target/action/date | Admin-sensitive | Later | Useful for finance/provider review. |
| `moderation_queue` | Reviews/media/content review | target, status, reason | status/type | Admin-sensitive | Later | Needed for marketplace scale. |
| `fraud_flags` | Risk events | user/provider/payment, reason, severity, status | status/severity | Admin-sensitive | Later | Start simple before automation. |

## Migration Rule For Future Work

Add only one vertical at a time with:

1. Migration.
2. Models/policies.
3. API requests/resources/controllers.
4. Admin resources.
5. Feature tests.
6. Flutter screens only after API is real.
7. No fake prices/statuses or public private-file paths.

## Sprint 36 Implementation Update

Implemented in Sprint 36:
- `hospital_profiles`
- `clinic_profiles`
- `medical_center_profiles`
- `radiology_profiles`
- `gym_profiles`
- `coach_profiles`
- `physiotherapy_profiles`
- `home_healthcare_profiles`
- `hospital_departments`
- `hospital_doctors`
- `service_categories`
- `provider_services`
- `provider_booking_settings`
- `provider_contracts`

Updated in Sprint 36:
- `providers.type` supports the expanded provider platform enum.
- `providers.status` supports `needs_changes`.
- `provider_branches` supports richer address/map/working-hour/service-radius fields.
- `provider_documents` supports explicit `visibility` and `approved_public_at`.

Implemented in Sprint 37:
- `radiology_scan_categories`
- `radiology_scans`
- `radiology_preparation_instructions`
- Safe provider/admin catalog management and read-only public-safe catalog filtering.

Still later:
- radiology order lifecycle.
- radiology result/report lifecycle.
- gym memberships/classes.
- coach sessions/plans/progress.
- home healthcare orders.
- insurance contracts.
- search engine tables/read models.
- production-scale partitioning/search infrastructure.

Privacy note:
- The new tables are foundation/admin/internal until each vertical has a complete backend lifecycle, public API safety review, Flutter UX, and tests.
