# Health Super App Product Map

Date: 2026-05-08

This document maps the intended Etamen product surface. It does not mean every module is implemented today.

## Summary Of Verticals

| Vertical | Current status | MVP phase | Production phase |
| --- | --- | --- | --- |
| Doctors | Implemented MVP | Current + pilot hardening | Strong search, media, reviews, insurance, teleconsultation later |
| Pharmacies | Implemented MVP foundation | Internal QA / later pilot scope | Delivery zones, inventory, pharmacist review, substitutes |
| Labs | Implemented MVP foundation | Internal QA / later pilot scope | Rich catalog, home collection ops, results UX |
| Radiology | Backend/admin catalog foundation | Internal catalog QA; no Flutter patient module yet | Full scan catalog/orders/results |
| Gyms | Not implemented | Design first | Memberships/classes/trainers |
| Fitness/Nutrition Coaches | Not implemented | Design first | Sessions/plans/progress |
| Personal Health | Implemented foundation | Current | Family, documents, chronic programs |
| AI Assistant | Implemented safety foundation | Current with strict scope | Context-aware but privacy-safe, document summaries later |
| Marketplace/Discovery | Basic DB discovery | Current + search UX | Geo/search engine/relevance ranking |
| Payments/Wallet | Implemented foundation | Current | Refunds, disputes, settlements automation |
| Admin/Ops | Strong Filament foundation | Current | Support/fraud/moderation ops |
| Notifications | Implemented foundation | Current | Production push/SMS/email/WhatsApp strategy |

## 1. Doctors

| Aspect | Plan |
| --- | --- |
| User journey | Search by specialty/area/availability, view doctor profile, choose branch/slot, book, pay if needed, receive reminders, review after completion. |
| Provider journey | Register, upload documents, get approved, manage branches/profile/specialties/schedules/slots, accept/reject/complete appointments, view wallet. |
| Admin journey | Approve provider/documents, manage specialties, monitor appointments/reviews/payments, handle disputes. |
| Required entities/tables | Existing: `providers`, `doctor_profiles`, `doctor_specialties`, `provider_branches`, `doctor_schedules`, `appointment_slots`, `appointments`, `appointment_reviews`. Future: `medical_subspecialties`, `doctor_subspecialties`, `doctor_services`, `doctor_media`, `doctor_insurances`. |
| Required APIs | Existing doctor listing/details/slots/booking/payment/review/provider schedule/admin. Future: richer filters, media, insurance, next available summary, teleconsultation. |
| Flutter screens | Existing doctors list/profile/booking/payment/appointments. Future: filters, map/list toggle, doctor media, reviews. |
| Admin screens | Existing Filament resources. Future: review moderation, media approval, dispute tooling. |
| Risks | Slot race conditions, fake ratings, provider document privacy, expensive availability queries. |
| MVP phase | Doctor-only pilot after physical payment gate. |
| Production phase | Search engine, media approval, review policy, SLA/ops. |

## 2. Pharmacies

| Aspect | Plan |
| --- | --- |
| User journey | Find nearby pharmacy, browse products, upload prescription if needed, create order, pay, track review/delivery. |
| Provider journey | Manage products, stock, prescription review, order status, wallet. |
| Admin journey | Approve pharmacies, review disputes, monitor orders/payments. |
| Required entities/tables | Existing: `pharmacy_profiles`, `pharmacy_products`, `pharmacy_prescriptions`, `pharmacy_orders`, `pharmacy_order_items`. Future: `pharmacy_inventory`, `pharmacy_delivery_zones`, `prescription_reviews`, product substitutes. |
| Required APIs | Existing public products/order/pay/prescription/provider/admin. Future: delivery fee quote, substitutes, pharmacist review notes. |
| Flutter screens | Existing pharmacies/products/cart/orders/prescription upload. Future: nearby map, delivery estimate, substitution approval. |
| Admin screens | Existing. Future: prescription review queue and stock alerts. |
| Risks | Prescription privacy, stock accuracy, delivery availability, unsafe medicine substitution. |
| MVP phase | Keep internal QA or include only after physical order/payment proof pass. |
| Production phase | Delivery zones, pharmacist SOPs, inventory sync. |

## 3. Labs

| Aspect | Plan |
| --- | --- |
| User journey | Search by lab/test/package, choose branch or home collection, order, pay, receive private results. |
| Provider journey | Manage tests/packages, accept/process orders, upload result file. |
| Admin journey | Approve labs, monitor results/orders/payments. |
| Required entities/tables | Existing: `lab_profiles`, `lab_tests`, `lab_packages`, `lab_package_items`, `lab_orders`, `lab_order_items`, `lab_results`. Future: `lab_test_categories`, richer result metadata. |
| Required APIs | Existing catalog/order/pay/results/provider/admin. Future: test preparation instructions and abnormal flag disclaimers. |
| Flutter screens | Existing labs/tests/cart/orders/results. Future: preparation instructions and richer result timeline. |
| Admin screens | Existing. Future: result quality audit queue. |
| Risks | Result privacy, abnormal wording must avoid diagnosis, file storage volume. |
| MVP phase | Internal QA unless explicitly scoped into first pilot. |
| Production phase | Private object storage and result delivery SLA. |

## 4. Radiology

| Aspect | Plan |
| --- | --- |
| User journey | Search radiology center by scan type/location/preparation, choose appointment/home if applicable, pay, receive report/images. |
| Provider journey | Manage scan catalog, preparation instructions, appointments/orders, upload reports/images. |
| Admin journey | Approve radiology centers, moderate catalog, monitor report delivery and disputes. |
| Required entities/tables | Implemented foundation: `radiology_profiles`, generic `provider_branches`, `radiology_scan_categories`, `radiology_scans`, `radiology_preparation_instructions`. Later: `radiology_orders`, `radiology_order_items`, `radiology_result_files`. |
| Required APIs | Implemented: safe read-only catalog, provider-owned scan management, admin category/scan/instruction management. Later: patient orders/pay/results and provider result upload. |
| Flutter screens | Not implemented in Sprint 37. Later: radiology list, scan catalog, cart/order, payment, result/report viewer. |
| Admin screens | Implemented: scan categories, scans, preparation text. Later: orders, result files, operational monitoring. |
| Risks | Large DICOM/image files, report privacy, preparation errors, regulatory requirements. |
| MVP phase | Backend/admin catalog foundation only; do not add dead Flutter links. |
| Production phase | Object storage/CDN/private download strategy, report SLA, ops SOP. |

## 5. Gyms

| Aspect | Plan |
| --- | --- |
| User journey | Find nearby gyms, view plans/classes/trainers, buy pass/subscription, receive reminders. |
| Provider journey | Manage branches, membership plans, classes, trainers, attendance if added later. |
| Admin journey | Approve gyms, review payments/refunds/disputes. |
| Required entities/tables | New: `gym_profiles`, `gym_branches`, `gym_membership_plans`, `gym_classes`, `gym_trainers`, `gym_reviews`. |
| Required APIs | Public gym discovery, plan/class catalog, purchase/session booking, provider/admin management. |
| Flutter screens | Gyms near me, gym details, plan checkout, class schedule. |
| Admin screens | Gym providers, plans, classes, trainer profiles, reviews. |
| Risks | Non-medical marketplace may dilute medical trust; cancellation/refund rules. |
| MVP phase | Research/design only. |
| Production phase | Operations and partner contracts. |

## 6. Fitness / Nutrition Coaches

| Aspect | Plan |
| --- | --- |
| User journey | Search coaches by specialty, view profile, book session/package, receive plan/progress tracking. |
| Provider journey | Manage profile/schedule/sessions/plans/progress notes. |
| Admin journey | Approve credentials, moderate claims, review disputes. |
| Required entities/tables | New: `coach_profiles`, `coach_specialties`, `coach_schedules`, `coach_sessions`, `coach_plans`, `coach_progress_logs`. |
| Required APIs | Public coach discovery, session booking/payment, provider plans/progress, admin approval. |
| Flutter screens | Coach list/profile/session booking/plans/progress. |
| Admin screens | Coach approval, specialty taxonomy, sessions/plans moderation. |
| Risks | Medical/nutrition claims, unsafe diet advice, credential validation. |
| MVP phase | Not in first pilot. |
| Production phase | Safety templates, credential review, complaint workflow. |

## 7. Personal Health

| Aspect | Plan |
| --- | --- |
| User journey | Maintain health profile, vitals, meds, reminders, family, documents, care plans. |
| Provider journey | With consent, view relevant context and assign plans. |
| Admin journey | Limited privacy-safe monitoring, audit access, support. |
| Required entities/tables | Existing health/medication/care plan tables. Future: `family_members`, `health_documents`, consent grants. |
| Required APIs | Existing health, meds, care plans. Future family/document vault/consent APIs. |
| Flutter screens | Existing health/meds/care plans. Future family switcher and document vault. |
| Admin screens | Existing health resources. Future support-only views with strict audit. |
| Risks | High privacy burden, consent, family access, data retention. |
| MVP phase | Current single-patient health tracking. |
| Production phase | Family/document vault with explicit consent/audit. |

## 8. AI Assistant

| Aspect | Plan |
| --- | --- |
| User journey | Ask safe health organization questions, receive non-diagnostic guidance, red-flag escalation. |
| Provider journey | Later: review patient-shared summaries, never hidden diagnosis automation. |
| Admin journey | Monitor safety events/usage/provider config without secrets. |
| Required entities/tables | Existing AI tables. Future document-summary jobs and consented context links. |
| Required APIs | Existing conversations/messages/context preview/quick ask/admin. Future document summarization with safe constraints. |
| Flutter screens | Existing AI chat/conversations/context preview. |
| Admin screens | Existing. |
| Risks | Diagnosis/treatment hallucination, privacy leakage, unsafe advice, provider key exposure. |
| MVP phase | Keep strict refusal/red-flag rules. |
| Production phase | Medical/legal review, monitoring, provider fallback, incident playbooks. |

## 9. Marketplace / Discovery

| Aspect | Plan |
| --- | --- |
| User journey | One search entry for doctors, pharmacies, labs, radiology, gyms, coaches by location/availability/price/rating. |
| Provider journey | Maintain discoverable profile/catalog. |
| Admin journey | Curate categories, synonyms, moderation, ranking policy. |
| Required entities/tables | Existing providers/cities/areas. Future `provider_locations`, `provider_search_index`, `service_search_index`, taxonomy tables. |
| Required APIs | Unified search endpoint later; current domain-specific listing APIs remain. |
| Flutter screens | Search results with filters, map/list, fallback when location denied. |
| Admin screens | Synonyms, categories, ranking flags. |
| Risks | Slow DB queries, wrong ranking, Arabic typo/synonym quality. |
| MVP phase | Domain lists and basic filters. |
| Production phase | Meilisearch/OpenSearch/Elasticsearch with geo. |

## 10. Payments / Wallet

| Aspect | Plan |
| --- | --- |
| User journey | Pay securely, upload manual proof when needed, see friendly status, request refund when applicable. |
| Provider journey | See wallet, transactions, withdrawals, settlements. |
| Admin journey | Review proofs, disputes, refunds, settlements. |
| Required entities/tables | Existing payment/wallet tables. Future dispute/refund workflow details. |
| Required APIs | Existing payment methods/status/proofs/Paymob/admin/wallet. Future refunds and dispute APIs. |
| Flutter screens | Existing payment screens. Future refund request/status. |
| Admin screens | Existing. Future dispute queue. |
| Risks | Fraud, duplicate callbacks, manual proof errors, refund/legal rules. |
| MVP phase | Finish physical proof/admin gate. |
| Production phase | Live Paymob, refund SOP, fraud monitoring. |

## 11. Admin / Operations

| Aspect | Plan |
| --- | --- |
| User journey | Support ticket/dispute visible later. |
| Provider journey | Provider onboarding/support/settlement transparency. |
| Admin journey | Approval, payment review, disputes, moderation, fraud, audit, support. |
| Required entities/tables | Existing admin resources and `audit_logs`. Future `support_tickets`, `admin_actions`, `moderation_queue`, `fraud_flags`. |
| Required APIs | Admin APIs and Filament resources. Future support/dispute APIs. |
| Flutter screens | Support entry exists; ticketing later. |
| Admin screens | Existing Filament; future dashboards. |
| Risks | Admin over-permission, audit gaps, support backlog. |
| MVP phase | Manual ops SOP. |
| Production phase | Role-specific admin permissions and dashboards. |

## 12. Notifications

| Aspect | Plan |
| --- | --- |
| User journey | Appointment, medication, payment, order, result, health reminders with preferences. |
| Provider journey | Order/appointment alerts. |
| Admin journey | Template management, dispatch monitoring, failure retries. |
| Required entities/tables | Existing notifications/tokens/preferences/templates/dispatches/scheduler. |
| Required APIs | Existing. Future production provider integration and marketing preference segmentation. |
| Flutter screens | Existing notification center/preferences. |
| Admin screens | Existing. |
| Risks | Sensitive payload leakage, noisy reminders, provider failures. |
| MVP phase | In-app and local/demo-safe. |
| Production phase | FCM/SMS/email/WhatsApp providers, queues, consent, quiet hours. |
