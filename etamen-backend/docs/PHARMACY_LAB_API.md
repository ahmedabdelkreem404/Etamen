# Pharmacy + Lab API

This document summarizes the local Sprint 66 patient/provider surface.

## Pharmacy Patient

- `GET /api/v1/pharmacies`
- `GET /api/v1/pharmacies/{pharmacy}`
- `GET /api/v1/pharmacies/{pharmacy}/products`
- `POST /api/v1/pharmacy/prescriptions`
- `GET /api/v1/pharmacy/prescriptions/{prescription}/download`
- `POST /api/v1/pharmacy/orders`
- `GET /api/v1/pharmacy/orders`
- `GET /api/v1/pharmacy/orders/{order}`
- `POST /api/v1/pharmacy/orders/{order}/pay`
- `POST /api/v1/pharmacy/orders/{order}/cancel`

Rules:

- backend calculates totals.
- patient sees own orders only.
- prescription file path is never returned.
- cancel is allowed only before payment flow starts.

## Pharmacy Provider

Workspace-scoped operations:

- `GET /api/v1/provider/workspace/{provider}/pharmacy/orders`
- `GET /api/v1/provider/workspace/{provider}/pharmacy/orders/{order}`
- `POST /api/v1/provider/workspace/{provider}/pharmacy/orders/{order}/accept`
- `POST /api/v1/provider/workspace/{provider}/pharmacy/orders/{order}/reject`
- `POST /api/v1/provider/workspace/{provider}/pharmacy/orders/{order}/preparing`
- `POST /api/v1/provider/workspace/{provider}/pharmacy/orders/{order}/ready`
- `POST /api/v1/provider/workspace/{provider}/pharmacy/orders/{order}/out-for-delivery`
- `POST /api/v1/provider/workspace/{provider}/pharmacy/orders/{order}/complete`
- `GET /api/v1/provider/workspace/{provider}/pharmacy/products`

Legacy provider operations:

- `GET /api/v1/provider/pharmacy/products`
- `POST /api/v1/provider/pharmacy/products`
- `GET /api/v1/provider/pharmacy/products/{product}`
- `PATCH /api/v1/provider/pharmacy/products/{product}`
- `DELETE /api/v1/provider/pharmacy/products/{product}`
- `GET /api/v1/provider/pharmacy/orders`
- `GET /api/v1/provider/pharmacy/orders/{order}`
- `PATCH /api/v1/provider/pharmacy/orders/{order}/status`

Provider access is scoped to the active pharmacy provider. Workspace-scoped actions require `manage_pharmacy_orders`, reject requires a reason, and responses expose prescription metadata only.

## Lab Patient

- `GET /api/v1/labs`
- `GET /api/v1/labs/{lab}`
- `GET /api/v1/labs/{lab}/tests`
- `GET /api/v1/labs/{lab}/packages`
- `POST /api/v1/lab/orders`
- `GET /api/v1/lab/orders`
- `GET /api/v1/lab/orders/{order}`
- `POST /api/v1/lab/orders/{order}/pay`
- `POST /api/v1/lab/orders/{order}/cancel`
- `GET /api/v1/lab/orders/{order}/results`
- `GET /api/v1/lab/results/{result}/download`

Rules:

- backend calculates totals.
- patient sees own orders only.
- lab result metadata is visible only when safe.
- raw result path is never returned.
- no diagnosis or medical interpretation is returned.
- cancel is allowed only before payment flow starts.

## Lab Provider

Workspace-scoped operations:

- `GET /api/v1/provider/workspace/{provider}/lab/orders`
- `GET /api/v1/provider/workspace/{provider}/lab/orders/{order}`
- `POST /api/v1/provider/workspace/{provider}/lab/orders/{order}/accept`
- `POST /api/v1/provider/workspace/{provider}/lab/orders/{order}/reject`
- `POST /api/v1/provider/workspace/{provider}/lab/orders/{order}/sample-scheduled`
- `POST /api/v1/provider/workspace/{provider}/lab/orders/{order}/sample-collected`
- `POST /api/v1/provider/workspace/{provider}/lab/orders/{order}/processing`
- `POST /api/v1/provider/workspace/{provider}/lab/orders/{order}/result-ready`
- `POST /api/v1/provider/workspace/{provider}/lab/orders/{order}/complete`
- `GET /api/v1/provider/workspace/{provider}/lab/catalog`

Legacy provider operations:

- `GET /api/v1/provider/lab/tests`
- `POST /api/v1/provider/lab/tests`
- `GET /api/v1/provider/lab/tests/{test}`
- `PATCH /api/v1/provider/lab/tests/{test}`
- `DELETE /api/v1/provider/lab/tests/{test}`
- `GET /api/v1/provider/lab/packages`
- `POST /api/v1/provider/lab/packages`
- `GET /api/v1/provider/lab/packages/{package}`
- `PATCH /api/v1/provider/lab/packages/{package}`
- `DELETE /api/v1/provider/lab/packages/{package}`
- `GET /api/v1/provider/lab/orders`
- `GET /api/v1/provider/lab/orders/{order}`
- `PATCH /api/v1/provider/lab/orders/{order}/status`
- `POST /api/v1/provider/lab/orders/{order}/results`

Provider access is scoped to the active lab provider. Workspace-scoped actions require `manage_lab_orders`, reject requires a reason, result metadata remains safe, and no medical interpretation is returned.

## Sprint 68 Local History Filters

Patient pharmacy history:

- `GET /api/v1/pharmacy/orders`
- safe filters: `status`, `payment_status`, `date_from`, `date_to`, `provider_id`, `search`, `order_number`, `per_page`.

Patient lab history:

- `GET /api/v1/lab/orders`
- safe filters: `status`, `payment_status`, `date_from`, `date_to`, `provider_id`, `visit_type`, `home_or_branch`, `search`, `order_number`, `per_page`.

Provider workspace history:

- `GET /api/v1/provider/workspace/{provider}/pharmacy/orders`
- `GET /api/v1/provider/workspace/{provider}/lab/orders`
- safe filters: `status`, `payment_status`, `date_from`, `date_to`, `patient_name`, `search`, `order_number`, `per_page`.

All list endpoints keep backend scoping as source of truth, cap pagination, return validation errors for invalid filters, and hide raw prescription/lab-result paths. Sprint 68 resources include safe UX metadata (`status_label_ar`, payment labels, backend action flags, and next-action labels) so Flutter displays status clarity without inventing permissions or payment state.
