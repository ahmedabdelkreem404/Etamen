# Sprint 44 Radiology Orders Backend

Date: 2026-05-09  
Scope: Local backend only

## Scope

Sprint 44 implements the local backend foundation for radiology orders, payments, and private result metadata.

This sprint does not:

- touch Hostinger or staging.
- add Flutter radiology screens.
- expose public launch readiness.
- add diagnosis, treatment, or interpretation logic.

## Tables Added

### `radiology_orders`

Stores the patient order header:

- `order_number`
- `patient_user_id`
- `provider_id`
- `branch_id`
- `status`
- `subtotal`
- `discount_amount`
- `total_amount`
- `payment_id`
- `scheduled_at`
- patient/provider notes
- lifecycle timestamps

### `radiology_order_items`

Stores immutable scan snapshots:

- scan id
- Arabic/English scan name snapshot
- Arabic/English category snapshot
- backend-owned unit price
- quantity
- total price
- preparation instruction snapshot

### `radiology_order_status_histories`

Tracks every meaningful status transition with actor, reason, and metadata.

### `radiology_results`

Stores private result/report metadata linked to `uploaded_files`.

Files are stored through the existing private medical file service. API responses never expose raw disk/path values.

## Statuses

Implemented status enum:

- `pending_payment`
- `pending_payment_review`
- `paid`
- `accepted`
- `in_progress`
- `result_ready`
- `completed`
- `cancelled_by_patient`
- `cancelled_by_provider`
- `rejected`

## Patient APIs

- `GET /api/v1/radiology/orders`
- `POST /api/v1/radiology/orders`
- `GET /api/v1/radiology/orders/{order}`
- `POST /api/v1/radiology/orders/{order}/cancel`
- `GET /api/v1/radiology/orders/{order}/results`
- `GET /api/v1/radiology/results/{result}/download`

Rules:

- only patients can create orders.
- provider must be approved, active, and type `radiology`.
- scans must be active and belong to the same provider.
- branch must belong to the same provider.
- backend calculates totals from `radiology_scans.base_price`.
- frontend cannot send status, subtotal, total, or payment id.
- patients can only see their own orders/results.

## Provider APIs

- `GET /api/v1/provider/radiology/orders`
- `GET /api/v1/provider/radiology/orders/{order}`
- `POST /api/v1/provider/radiology/orders/{order}/accept`
- `POST /api/v1/provider/radiology/orders/{order}/reject`
- `POST /api/v1/provider/radiology/orders/{order}/start`
- `POST /api/v1/provider/radiology/orders/{order}/mark-result-ready`
- `POST /api/v1/provider/radiology/orders/{order}/complete`
- `POST /api/v1/provider/radiology/orders/{order}/results`

Rules:

- only owner/staff of the same radiology provider can access/manage.
- provider cannot access another provider order.
- provider cannot mark payment verified.
- result upload uses private storage.
- patient visibility is controlled by `is_visible_to_patient`.

## Admin APIs

- `GET /api/v1/admin/radiology-orders`
- `GET /api/v1/admin/radiology-orders/{order}`
- `POST /api/v1/admin/radiology-orders/{order}/force-cancel`
- `GET /api/v1/admin/radiology-orders/{order}/status-history`
- `POST /api/v1/admin/radiology-orders/{order}/results`

Admin can filter by provider, status, date, and patient.

## Payment Integration

Radiology order creation immediately creates a payment when `total_amount > 0`.

Payment linkage:

- `payments.payable_type = App\Modules\Radiology\Infrastructure\Models\RadiologyOrder`
- `payments.payable_id = radiology_orders.id`
- `provider_type = radiology`

Manual payment flow:

1. Patient selects Vodafone Cash/InstaPay.
2. Patient uploads proof.
3. Payment moves to `pending_review`.
4. Radiology order moves to `pending_payment_review`.
5. Admin accepts proof.
6. Payment moves to `verified`.
7. Radiology order moves to `paid`.
8. Invoice is created.

Payment rejection returns the order to `pending_payment`.

## Result Privacy

Result upload uses existing private file storage:

- disk remains `medical_private`.
- category uses `medical_report` with radiology metadata.
- raw `disk` and `path` are never returned in patient/provider/admin JSON resources.
- patient can download only their own visible result.
- provider can download own provider order result.
- admin can access all.

## Filament

Added operational resources:

- `RadiologyOrderResource`
- `RadiologyOrderItemResource`
- `RadiologyResultResource`
- `RadiologyOrderStatusHistoryResource`

These are operational/admin views, not patient launch screens.

## Seed Data

Existing `PilotDemoSeeder` already seeds:

- approved demo radiology provider.
- active branch.
- active scan catalog with prices.
- preparation instructions.

No fake production data was added.

## Tests

Added:

```text
tests/Feature/RadiologyOrdersBackendSprint44Test.php
```

Coverage:

- patient can create radiology order.
- backend calculates totals.
- frontend cannot force total/status/payment.
- inactive scan, unapproved provider, and wrong branch rejected.
- manual proof upload works.
- admin accept verifies payment and moves order to `paid`.
- patient cannot verify payment.
- provider access is scoped.
- non-radiology provider is blocked from radiology provider APIs.
- provider uploads private result.
- patient sees only visible result metadata.
- unauthorized result download is forbidden.
- raw private paths are not exposed.
- status history and admin filters work.

## Remaining Work For Sprint 45

- Flutter radiology catalog and order screens.
- Patient radiology payment screen integration in Flutter.
- Result list/download UI.
- Provider-facing operational UI beyond admin APIs.
- Refund/cancellation policy for paid radiology orders.
- Staging deployment and physical-device QA.

## Decision

```text
LOCAL_RADIOLOGY_ORDERS_BACKEND_ACCEPTED
```

This is local backend acceptance only. It does not approve Flutter radiology, staging, public launch, or production readiness.
