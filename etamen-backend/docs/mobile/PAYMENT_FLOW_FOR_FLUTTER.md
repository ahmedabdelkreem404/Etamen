# Payment Flow For Flutter

Flutter must treat the backend as the source of truth for payment state. Flutter never sends trusted amount, never marks a payment verified, and never stores Paymob secrets.

## Doctor Appointment

1. Patient books appointment: `POST /api/v1/appointments`.
2. If appointment status is `confirmed`, it is free and no payment is required.
3. If appointment status is `pending_payment`, read `payment_id`.
4. Load methods: `GET /api/v1/payment-methods`.
5. Manual flow:
   - `POST /api/v1/payments/{payment}/manual/select`
   - Show returned instructions.
   - `POST /api/v1/payments/{payment}/proofs` as multipart.
   - Poll `GET /api/v1/payments/{payment}/status`.
   - `verified` means appointment becomes `confirmed`.
   - `rejected` means show retry upload/select option.
6. Paymob flow:
   - `POST /api/v1/payments/{payment}/paymob/create-session`
   - Open returned checkout URL/client data.
   - Redirect/success page is not proof.
   - Poll payment status until `verified`, `failed`, `expired`, or `cancelled`.

## Pharmacy Order

1. Create order: `POST /api/v1/pharmacy/orders`.
2. Pharmacy accepts the order through provider backend.
3. Patient calls `POST /api/v1/pharmacy/orders/{order}/pay`.
4. Use the same manual or Paymob payment flow.
5. Verified payment moves order payment status to `paid` and order status to `paid`.

## Lab Order

1. Create order: `POST /api/v1/lab/orders`.
2. Lab accepts the order.
3. Patient calls `POST /api/v1/lab/orders/{order}/pay`.
4. Use the same manual or Paymob payment flow.
5. Verified payment moves lab order to `paid`.

## UI State Recommendations

| Payment status | Flutter behavior |
| --- | --- |
| `awaiting_method` | Ask user to choose Paymob/Vodafone/InstaPay. |
| `awaiting_proof` | Show proof upload. |
| `pending_review` | Show waiting for admin review. |
| `pending_gateway` | Show waiting for Paymob confirmation; poll safely. |
| `verified` | Show paid/confirmed state and stop polling. |
| `rejected` | Show rejection reason if available and retry proof. |
| `failed` | Show payment failed and allow retry if backend state allows. |

## Polling

- Start with 3-5 second intervals, then back off.
- Stop polling on terminal statuses.
- Do not poll faster than backend rate limits.

## Deferred

Refund automation and Paymob provider transfers are not implemented yet.
