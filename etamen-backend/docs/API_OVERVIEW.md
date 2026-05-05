# Etamen API Overview

Etamen backend exposes versioned JSON APIs under `/api/v1`.

All API responses must follow:

```json
{
  "success": true,
  "message": "OK",
  "data": {},
  "errors": {}
}
```

## Core Modules

- Identity: register, login, logout, current user.
- Providers: doctors, pharmacies, labs, approvals, branches, documents.
- Appointments: doctor schedules, slots, booking, lifecycle, reviews.
- Payments: Paymob, manual Vodafone Cash, manual InstaPay, invoices.
- Wallets: provider ledger, commission, withdrawals, settlements.
- Pharmacies: products, prescriptions, orders.
- Labs: tests, packages, orders, private result PDFs.
- Health: patient profile and vitals tracking.
- Medications: reminder schedule and adherence logs.
- CarePlans: nutrition/care plan structure and commitment tracking.
- AI: safety-gated assistant, context preview, admin monitoring.
- Notifications: in-app notifications, tokens, preferences, dispatch queue.

## Authentication

Use Laravel Sanctum bearer tokens for protected routes.

Public routes are intentionally limited to safe discovery, health, payment callbacks, and catalog browsing. Medical, payment, wallet, AI, and notification APIs require authentication.

## Pagination And Limits

List endpoints either paginate or apply a bounded `per_page` limit. Default values are module-specific, and maximum `per_page` is 100.

## Deferred

- Flutter UI integration.
- Real FCM/SMS/WhatsApp providers.
- Refund automation.
- Paymob provider transfers.
- Live AI credentials and live Paymob credentials validation.
