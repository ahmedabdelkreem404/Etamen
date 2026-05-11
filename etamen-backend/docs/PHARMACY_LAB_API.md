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

- `GET /api/v1/provider/pharmacy/products`
- `POST /api/v1/provider/pharmacy/products`
- `GET /api/v1/provider/pharmacy/products/{product}`
- `PATCH /api/v1/provider/pharmacy/products/{product}`
- `DELETE /api/v1/provider/pharmacy/products/{product}`
- `GET /api/v1/provider/pharmacy/orders`
- `GET /api/v1/provider/pharmacy/orders/{order}`
- `PATCH /api/v1/provider/pharmacy/orders/{order}/status`

Provider access is scoped to the active pharmacy provider.

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

Provider access is scoped to the active lab provider.
