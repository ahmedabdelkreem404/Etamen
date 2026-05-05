# API Route Inventory

This inventory covers the mobile and admin API contract under `/api/v1`. Admin-only endpoints are included for completeness but are not Flutter patient MVP priorities.

Legend:

- Auth: `public`, `auth`, `patient`, `provider`, `admin`.
- Priority: `required_for_mvp`, `later`, `admin_only`.
- Pagination: `yes` means send `page`/`per_page` where supported; `bounded` means backend caps results but may not return explicit pagination metadata.

## Auth

| Method | Path | Auth | Purpose | Request DTO | Response DTO | Pagination | Upload | Priority |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| POST | `/api/v1/auth/register` | public | Register patient user | RegisterRequest | AuthSession | no | no | required_for_mvp |
| POST | `/api/v1/auth/login` | public | Login and issue Sanctum token | LoginRequest | AuthSession | no | no | required_for_mvp |
| POST | `/api/v1/auth/logout` | auth | Revoke current token | none | Empty | no | no | required_for_mvp |
| GET | `/api/v1/me` | auth | Current user | none | UserResource | no | no | required_for_mvp |

## Profile

| Method | Path | Auth | Purpose | Request DTO | Response DTO | Pagination | Upload | Priority |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| GET | `/api/v1/profile` | patient | Patient profile | none | PatientProfileResource | no | no | required_for_mvp |
| PUT | `/api/v1/profile` | patient | Update patient profile | PatientProfileRequest | PatientProfileResource | no | no | required_for_mvp |

## Providers

| Method | Path | Auth | Purpose | Request DTO | Response DTO | Pagination | Upload | Priority |
| --- | --- | --- | --- | --- | --- | --- | --- |
| GET | `/api/v1/doctors` | public | Public doctors list | DoctorFilters | ProviderResource[] | bounded | no | required_for_mvp |
| GET | `/api/v1/doctors/{doctor}` | public | Doctor profile | none | ProviderResource | no | no | required_for_mvp |
| GET | `/api/v1/pharmacies` | public | Public pharmacies | ProviderFilters | ProviderResource[] | bounded | no | required_for_mvp |
| GET | `/api/v1/pharmacies/{pharmacy}` | public | Pharmacy profile | none | ProviderResource | no | no | required_for_mvp |
| GET | `/api/v1/labs` | public | Public labs | ProviderFilters | ProviderResource[] | bounded | no | required_for_mvp |
| GET | `/api/v1/labs/{lab}` | public | Lab profile | none | ProviderResource | no | no | required_for_mvp |
| GET | `/api/v1/specialties` | public | Specialties | none | SpecialtyResource[] | bounded | no | required_for_mvp |
| GET | `/api/v1/cities` | public | Cities | none | CityResource[] | bounded | no | required_for_mvp |
| GET | `/api/v1/areas` | public | Areas | `city_id` optional | AreaResource[] | bounded | no | required_for_mvp |

## Doctors And Appointments

| Method | Path | Auth | Purpose | Request DTO | Response DTO | Pagination | Upload | Priority |
| --- | --- | --- | --- | --- | --- | --- | --- |
| GET | `/api/v1/doctors/{doctor}/slots` | public | Available doctor slots | SlotQuery | AppointmentSlotResource[] | bounded | no | required_for_mvp |
| GET | `/api/v1/appointments` | patient | Own appointments | AppointmentFilters | AppointmentResource[] | yes | no | required_for_mvp |
| POST | `/api/v1/appointments` | patient | Book appointment | BookAppointmentRequest | AppointmentResource | no | no | required_for_mvp |
| GET | `/api/v1/appointments/{appointment}` | patient owner | Appointment details | none | AppointmentResource | no | no | required_for_mvp |
| POST | `/api/v1/appointments/{appointment}/cancel` | patient owner | Cancel allowed appointment | CancelAppointmentRequest | AppointmentResource | no | no | required_for_mvp |
| POST | `/api/v1/appointments/{appointment}/review` | patient owner | Review completed appointment | AppointmentReviewRequest | AppointmentReviewResource | no | no | later |

Provider doctor scheduling and appointment actions live under `/api/v1/provider/doctor/*` and `/api/v1/provider/appointments/*`; these are provider app/admin tools, not patient MVP.

## Payments

| Method | Path | Auth | Purpose | Request DTO | Response DTO | Pagination | Upload | Priority |
| --- | --- | --- | --- | --- | --- | --- | --- |
| GET | `/api/v1/payment-methods` | public | Active payment methods | none | PaymentMethodResource[] | bounded | no | required_for_mvp |
| GET | `/api/v1/payments/{payment}/status` | owner/provider/admin | Safe payment status | none | PaymentStatusResource | no | no | required_for_mvp |
| POST | `/api/v1/payments/{payment}/manual/select` | patient owner | Select Vodafone/InstaPay manual method | ManualSelectRequest | PaymentStatusResource | no | no | required_for_mvp |
| POST | `/api/v1/payments/{payment}/proofs` | patient owner | Upload manual proof | PaymentProofUploadRequest | PaymentProofResource | no | yes | required_for_mvp |
| POST | `/api/v1/payments/{payment}/paymob/create-session` | patient owner | Create Paymob checkout session | PaymobSessionRequest | Safe checkout data | no | no | required_for_mvp |
| POST | `/api/v1/payments/paymob/callback` | public HMAC | Paymob callback | PaymobPayload | PaymentStatusResource | no | no | backend_only |
| POST | `/api/v1/payments/paymob/webhook` | public HMAC | Paymob webhook | PaymobPayload | PaymentStatusResource | no | no | backend_only |

## Wallet

| Method | Path | Auth | Purpose | Request DTO | Response DTO | Pagination | Upload | Priority |
| --- | --- | --- | --- | --- | --- | --- | --- |
| GET | `/api/v1/provider/wallet` | provider | Own provider wallet | none | WalletResource | no | no | later |
| GET | `/api/v1/provider/wallet/transactions` | provider | Own ledger | WalletTransactionFilters | WalletTransactionResource[] | yes | no | later |
| POST | `/api/v1/provider/withdrawals` | provider | Request withdrawal | WithdrawalRequest | WithdrawalRequestResource | no | no | later |
| GET | `/api/v1/provider/withdrawals` | provider | Own withdrawals | none | WithdrawalRequestResource[] | yes | no | later |

## Pharmacies

| Method | Path | Auth | Purpose | Request DTO | Response DTO | Pagination | Upload | Priority |
| --- | --- | --- | --- | --- | --- | --- | --- |
| GET | `/api/v1/pharmacies/{pharmacy}/products` | public | Active pharmacy products | ProductFilters | PharmacyProductResource[] | bounded | no | required_for_mvp |
| POST | `/api/v1/pharmacy/prescriptions` | patient | Upload private prescription | PrescriptionUploadRequest | PharmacyPrescriptionResource | no | yes | required_for_mvp |
| GET | `/api/v1/pharmacy/prescriptions/{prescription}/download` | authorized | Download prescription | none | file stream | no | no | required_for_mvp |
| GET | `/api/v1/pharmacy/orders` | patient | Own pharmacy orders | filters | PharmacyOrderResource[] | yes | no | required_for_mvp |
| POST | `/api/v1/pharmacy/orders` | patient | Create pharmacy order | PharmacyOrderRequest | PharmacyOrderResource | no | no | required_for_mvp |
| GET | `/api/v1/pharmacy/orders/{order}` | patient owner | Order details | none | PharmacyOrderResource | no | no | required_for_mvp |
| POST | `/api/v1/pharmacy/orders/{order}/pay` | patient owner | Create order payment | none | PharmacyOrderResource | no | no | required_for_mvp |

## Labs

| Method | Path | Auth | Purpose | Request DTO | Response DTO | Pagination | Upload | Priority |
| --- | --- | --- | --- | --- | --- | --- | --- |
| GET | `/api/v1/labs/{lab}/tests` | public | Active lab tests | filters | LabTestResource[] | bounded | no | required_for_mvp |
| GET | `/api/v1/labs/{lab}/packages` | public | Active lab packages | filters | LabPackageResource[] | bounded | no | required_for_mvp |
| GET | `/api/v1/lab/orders` | patient | Own lab orders | filters | LabOrderResource[] | yes | no | required_for_mvp |
| POST | `/api/v1/lab/orders` | patient | Create lab order | LabOrderRequest | LabOrderResource | no | no | required_for_mvp |
| GET | `/api/v1/lab/orders/{order}` | patient owner | Lab order details | none | LabOrderResource | no | no | required_for_mvp |
| POST | `/api/v1/lab/orders/{order}/pay` | patient owner | Create lab payment | none | LabOrderResource | no | no | required_for_mvp |
| GET | `/api/v1/lab/orders/{order}/results` | patient owner | Visible results | none | LabResultResource[] | bounded | no | required_for_mvp |
| GET | `/api/v1/lab/results/{result}/download` | authorized | Secure result download | none | file stream | no | no | required_for_mvp |

## Health And Vitals

| Method | Path | Auth | Purpose | Request DTO | Response DTO | Pagination | Upload | Priority |
| --- | --- | --- | --- | --- | --- | --- | --- |
| GET | `/api/v1/health/profile` | patient | Own health profile | none | HealthProfileResource | no | no | required_for_mvp |
| PUT | `/api/v1/health/profile` | patient | Update health profile | HealthProfileRequest | HealthProfileResource | no | no | required_for_mvp |
| GET | `/api/v1/health/vitals` | patient | Own vital records | filters | VitalRecordResource[] | yes | no | required_for_mvp |
| POST | `/api/v1/health/vitals` | patient | Add vital | VitalRecordRequest | VitalRecordResource | no | no | required_for_mvp |
| GET | `/api/v1/health/vitals/trends` | patient | Trends | TrendQuery | VitalTrendResource | bounded | no | required_for_mvp |
| GET | `/api/v1/health/vitals/latest` | patient | Latest per vital type | none | VitalRecordResource[] | no | no | required_for_mvp |
| GET | `/api/v1/health/summary` | patient | Safe health summary | none | HealthSummaryResource | no | no | required_for_mvp |

Other health profile data endpoints: `/health/chronic-diseases`, `/health/allergies`, `/health/current-medications`, `/health/surgeries`, `/health/goals`.

## Medications

| Method | Path | Auth | Purpose | Request DTO | Response DTO | Pagination | Upload | Priority |
| --- | --- | --- | --- | --- | --- | --- | --- |
| GET | `/api/v1/medications/reminders` | patient | Own reminders | filters | MedicationReminderResource[] | yes | no | required_for_mvp |
| POST | `/api/v1/medications/reminders` | patient | Create reminder | MedicationReminderRequest | MedicationReminderResource | no | no | required_for_mvp |
| POST | `/api/v1/medications/reminders/{reminder}/taken` | patient owner | Mark taken | MedicationLogRequest | MedicationLogResource | no | no | required_for_mvp |
| POST | `/api/v1/medications/reminders/{reminder}/skipped` | patient owner | Mark skipped | MedicationLogRequest | MedicationLogResource | no | no | required_for_mvp |
| GET | `/api/v1/medications/today` | patient | Today schedule | none | schedule DTO | bounded | no | required_for_mvp |
| GET | `/api/v1/medications/upcoming` | patient | Upcoming schedule | filters | schedule DTO | bounded | no | required_for_mvp |
| GET | `/api/v1/medications/adherence` | patient | Adherence summary | date range | MedicationAdherenceResource | bounded | no | required_for_mvp |

## CarePlans

| Method | Path | Auth | Purpose | Request DTO | Response DTO | Pagination | Upload | Priority |
| --- | --- | --- | --- | --- | --- | --- | --- |
| GET | `/api/v1/care-plans` | patient | Own care plans | filters | CarePlanResource[] | yes | no | required_for_mvp |
| POST | `/api/v1/care-plans` | patient | Create own plan | CarePlanRequest | CarePlanResource | no | no | required_for_mvp |
| POST | `/api/v1/care-plans/{plan}/checkins` | patient owner | Upsert check-in | CarePlanCheckinRequest | CarePlanCheckinResource | no | no | required_for_mvp |
| POST | `/api/v1/care-plans/{plan}/meal-logs` | patient owner | Log meal | MealLogRequest | MealLogResource | no | optional image | required_for_mvp |
| GET | `/api/v1/care-plans/{plan}/progress` | patient owner/provider/admin | Progress summary | date range | CarePlanProgressResource | bounded | no | required_for_mvp |
| GET | `/api/v1/care-plans/summary` | patient | Own plan summary | none | CarePlanSummaryResource | no | no | required_for_mvp |

## AI

| Method | Path | Auth | Purpose | Request DTO | Response DTO | Pagination | Upload | Priority |
| --- | --- | --- | --- | --- | --- | --- | --- |
| GET | `/api/v1/ai/conversations` | patient | Own conversations | filters | AiConversationResource[] | yes | no | required_for_mvp |
| POST | `/api/v1/ai/conversations` | patient | Create conversation | AiConversationRequest | AiConversationResource | no | no | required_for_mvp |
| POST | `/api/v1/ai/conversations/{conversation}/messages` | patient owner | Send message | AiMessageRequest | AiMessageResource | no | no | required_for_mvp |
| GET | `/api/v1/ai/conversations/{conversation}/messages` | patient owner | Conversation messages | filters | AiMessageResource[] | yes | no | required_for_mvp |
| POST | `/api/v1/ai/ask` | patient | Quick ask | AiAskRequest | AiMessageResource | no | no | required_for_mvp |
| GET | `/api/v1/ai/context-preview` | patient | Safe context preview | none | AiContextPreviewResource | no | no | required_for_mvp |

## Notifications

| Method | Path | Auth | Purpose | Request DTO | Response DTO | Pagination | Upload | Priority |
| --- | --- | --- | --- | --- | --- | --- | --- |
| GET | `/api/v1/notifications` | auth | Own notifications | filters | NotificationResource[] | yes | no | required_for_mvp |
| GET | `/api/v1/notifications/unread-count` | auth | Unread count | none | `{unread_count}` | no | no | required_for_mvp |
| POST | `/api/v1/notifications/{notification}/read` | owner | Mark read | none | NotificationResource | no | no | required_for_mvp |
| POST | `/api/v1/notifications/read-all` | auth | Mark all read | none | `{updated_count}` | no | no | required_for_mvp |
| GET | `/api/v1/notification-tokens` | auth | Own device tokens | none | NotificationTokenResource[] | yes | no | required_for_mvp |
| POST | `/api/v1/notification-tokens` | auth | Register token | NotificationTokenRequest | NotificationTokenResource | no | no | required_for_mvp |
| GET | `/api/v1/notification-preferences` | auth | Preferences | none | NotificationPreferenceResource[] | no | no | required_for_mvp |
| PUT | `/api/v1/notification-preferences` | auth | Update preferences | NotificationPreferenceRequest | NotificationPreferenceResource[] | no | no | required_for_mvp |

## System

| Method | Path | Auth | Purpose | Request DTO | Response DTO | Pagination | Upload | Priority |
| --- | --- | --- | --- | --- | --- | --- | --- |
| GET | `/api/v1/system/health` | public | Minimal app health | none | SystemHealth | no | no | required_for_mvp |
| GET | `/api/v1/system/readiness` | admin | Operational checks | none | SystemReadiness | no | no | admin_only |

## Admin

Admin APIs are under `/api/v1/admin/*`. They require `auth:sanctum` and admin middleware and are `admin_only` for Flutter MVP. Groups include providers, appointments, payments, invoices, pharmacy orders, lab orders/results, health, medications, care plans, AI monitoring, notifications, wallets, commission rules, withdrawals, settlements, and scheduler runs.
