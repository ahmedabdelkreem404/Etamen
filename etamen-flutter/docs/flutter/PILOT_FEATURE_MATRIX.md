# Etamen Pilot Feature Matrix

| Feature | Backend ready | Flutter ready | Production ready | Notes |
| --- | --- | --- | --- | --- |
| Auth | Ready | Ready | Partial | Needs staging domain/session smoke. |
| Doctors | Ready | Ready | Partial | Needs approved pilot providers and schedules. |
| Appointments | Ready | Ready | Partial | Needs real slots and cancellation policy review. |
| Payments manual | Ready | Ready | Partial | Manual methods/admin review process must be staffed. |
| Paymob | Partial | Partial | Blocked | Backend live config/callback validation required for production. |
| Pharmacy | Ready | Ready | Partial | Needs pilot pharmacy data/products and manual operations. |
| Labs | Ready | Ready | Partial | Needs pilot lab tests/results process and file download smoke. |
| Health/Vitals | Ready | Ready | Partial | Safe for tracking; no clinical decision claims. |
| Medications | Ready | Ready | Partial | Reminder organization only; no notification scheduling yet. |
| Care Plans | Ready | Ready | Partial | Consumption/tracking ready; provider builder not in Flutter. |
| Notifications in-app | Ready | Ready | Partial | In-app ready; token foundation local only. |
| Push notifications | Partial | Deferred | Blocked | Real FCM/APNS not configured in Flutter. |
| AI assistant | Ready if backend provider configured | Ready | Partial | Backend credentials/limits/monitoring required. |
| Account/legal/support | Ready | Ready | Partial | Legal review and real support contacts required. |
| Refunds | Partial | Deferred | Blocked | No refund automation; support/admin review only. |
| Provider dashboards | Ready backend-side where implemented | Deferred | Deferred | Patient app only. |
| Admin operations | Ready backend-side where implemented | Deferred | Partial | Admin must handle payments/orders manually. |
| Queue/scheduler | Ready backend-side | Not applicable | Partial | Ops must run workers/cron in staging/pilot. |
| Private files | Ready | Ready | Partial | Storage permissions/downloads need staging smoke. |
| Monitoring/logging | Partial | Not applicable | Partial | Backend/app crash monitoring not fully configured. |

## Pilot Interpretation

Etamen is a conditional pilot candidate for a small supervised group after staging data, legal/support contacts, backend workers, and real-device E2E checks are completed.

It is not yet a public launch candidate because signing, Play Store readiness, live push, refund operations, production monitoring, and legal review are still pending.
